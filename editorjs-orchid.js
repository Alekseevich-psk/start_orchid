// editorjs-orchid.js
// Только .mjs, без CSS, без дублей

import fs from 'fs';
import path from 'path';
import fse from 'fs-extra';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PROJECT_ROOT = __dirname;
const NODE_MODULES = path.join(PROJECT_ROOT, 'node_modules');
const EDITORJS_DIR = path.join(NODE_MODULES, '@editorjs');
const PUBLIC_ORCHID_JS = path.join(PROJECT_ROOT, 'public', 'vendor', 'orchid', 'js');
const CONFIG_PLATFORM_PATH = path.join(PROJECT_ROOT, 'config', 'platform.php');

// Плагины, которые НЕ нужно копировать
const EXCLUDE_PLUGINS = ['list'];

// Читаем config/platform.php
function readConfig() {
    if (!fs.existsSync(CONFIG_PLATFORM_PATH)) {
        throw new Error(`Файл config/platform.php не найден: ${CONFIG_PLATFORM_PATH}`);
    }
    return fs.readFileSync(CONFIG_PLATFORM_PATH, 'utf8');
}

// Записываем обновлённый config
function writeConfig(content) {
    fs.writeFileSync(CONFIG_PLATFORM_PATH, content, 'utf8');
    console.log('✅ config/platform.php обновлён');
}

// Получаем список плагинов
function getEditorJsPlugins() {
    if (!fs.existsSync(EDITORJS_DIR)) {
        console.warn('⚠️ @editorjs не установлен. Выполни: npm install @editorjs/editorjs');
        return [];
    }

    const dirs = fs.readdirSync(EDITORJS_DIR);
    return dirs.filter(dir => {
        const pluginDir = path.join(EDITORJS_DIR, dir);
        const packageJson = path.join(pluginDir, 'package.json');
        if (EXCLUDE_PLUGINS.includes(dir)) return false;
        if (!fs.existsSync(packageJson)) return false;

        try {
            const pkg = JSON.parse(fs.readFileSync(packageJson, 'utf8'));
            return pkg.module || pkg.browser || pkg.main;
        } catch (e) {
            return false;
        }
    });
}

// Копируем .mjs файлы и возвращаем пути
function copyAssetsAndGetPaths() {
    const scripts = [];

    // Создаём папку
    fse.ensureDirSync(PUBLIC_ORCHID_JS);

    // === КОПИРУЕМ ОСНОВНОЙ EDITOR.JS (.mjs) ===
    const possibleJsPaths = [
        path.join(EDITORJS_DIR, 'editorjs', 'dist', 'index.mjs'),
        path.join(EDITORJS_DIR, 'editorjs', 'dist', 'editorjs.mjs'),
    ];

    const editorJsSrc = possibleJsPaths.find(p => fs.existsSync(p));

    if (!editorJsSrc) {
        console.error('❌ Не найден index.mjs или editorjs.mjs. Установи: npm install @editorjs/editorjs');
        process.exit(1);
    }

    const editorJsDest = path.join(PUBLIC_ORCHID_JS, 'editor.mjs');
    fse.copySync(editorJsSrc, editorJsDest);
    scripts.push('/vendor/orchid/js/editor.mjs');
    console.log('✅ editor.mjs скопирован');

    // === КОПИРУЕМ ПЛАГИНЫ (.mjs) ===
    const plugins = getEditorJsPlugins();
    console.log(`🔍 Найдено плагинов: ${plugins.length}`);

    plugins.forEach(pluginName => {
        const pluginDir = path.join(EDITORJS_DIR, pluginName);
        const distDir = path.join(pluginDir, 'dist');

        if (!fs.existsSync(distDir)) {
            console.warn(`⚠️ Нет /dist в ${pluginName}, пропускаем`);
            return;
        }

        const files = fs.readdirSync(distDir);
        const mjsFiles = files.filter(f => f.endsWith('.mjs'));

        if (mjsFiles.length === 0) {
            console.log(`🟡 ${pluginName} — .mjs не найден, пропускаем`);
            return;
        }

        mjsFiles.forEach(file => {
            const src = path.join(distDir, file);
            const dest = path.join(PUBLIC_ORCHID_JS, `${pluginName}.mjs`);
            fse.copySync(src, dest);
            scripts.push(`/vendor/orchid/js/${pluginName}.mjs`);
            console.log(`✅ ${pluginName}.mjs скопирован`);
        });
    });

    return { scripts };
}

// Вставляем JS в config/platform.php
function injectIntoConfig(config, scripts) {
    let output = config;

    // Удаляем старые записи Editor.js / @editorjs
    output = output.replace(
        /\/vendor\/orchid\/js\/(?:editor|@editorjs\/[^'"]+)\.(?:js|mjs)',?\s*/g,
        ''
    );

    // Вставляем JS
    const scriptsPattern = /'scripts'\s*=>\s*\[/;
    const scriptsMatch = output.match(scriptsPattern);
    if (scriptsMatch) {
        const insertPoint = scriptsMatch.index + scriptsMatch[0].length;
        const newScripts = scripts.map(s => `        '${s}'`).join(",\n        ");
        output = output.slice(0, insertPoint) + `\n        ${newScripts},` + output.slice(insertPoint);
    }

    return output;
}

// Основная функция
async function main() {
    console.log('🚀 Запуск интеграции Editor.js с Orchid...');

    try {
        const { scripts } = copyAssetsAndGetPaths();

        if (scripts.length === 0) {
            console.log('❌ Нечего копировать. Убедись, что установлены пакеты @editorjs/*');
            process.exit(1);
        }

        let config = readConfig();
        config = injectIntoConfig(config, scripts);
        writeConfig(config);

        console.log('🎉 Готово! Editor.js (.mjs) и плагины интегрированы в Orchid.');
        console.log('📌 Перезагрузи админку и проверь консоль.');
    } catch (err) {
        console.error('❌ Ошибка:', err.message);
        process.exit(1);
    }
}

main();