/**
 * Сервис для работы с картой сайта
 * Предоставляет данные для навигации, хлебных крошек и sitemap
 */

class SiteMapService {
    constructor(menuTree) {
        this.menuTree = menuTree || [];
    }

    /**
     * Получить плоский список всех пунктов
     */
    getAllItems() {
        const items = [];
        const flatten = (tree) => {
            tree.forEach(item => {
                items.push(item);
                if (item.children) {
                    flatten(item.children);
                }
            });
        };
        flatten(this.menuTree);
        return items;
    }

    /**
     * Найти пункт по URL
     */
    findByUrl(url) {
        const items = this.getAllItems();
        return items.find(item => item.url === url);
    }

    /**
     * Получить цепочку хлебных крошек для URL
     */
    getBreadcrumbsForUrl(url) {
        const path = [];
        const findPath = (node, targetUrl) => {
            if (node.url === targetUrl) {
                path.push(node);
                return true;
            }

            if (node.children) {
                for (const child of node.children) {
                    if (findPath(child, targetUrl)) {
                        path.unshift(node);
                        return true;
                    }
                }
            }

            return false;
        };

        for (const rootItem of this.menuTree) {
            if (findPath(rootItem, url)) {
                return path;
            }
        }

        return [];
    }

    /**
     * Получить sitemap в формате для генерации XML
     */
    getSitemap() {
        const items = this.getAllItems();
        return items.map(item => ({
            loc: item.url,
            lastmod: item.updated_at || new Date().toISOString().split('T')[0],
            changefreq: 'weekly',
            priority: item.priority || '0.8'
        }));
    }
}

// Глобальный экземпляр (ожидает, что window.siteMenuTree будет определён)
let siteMap = null;

document.addEventListener('DOMContentLoaded', () => {
    if (window.siteMenuTree) {
        siteMap = new SiteMapService(window.siteMenuTree);
    }
});

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SiteMapService;
}
