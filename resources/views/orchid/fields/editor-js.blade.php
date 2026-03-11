@component($typeForm, get_defined_vars())
    <div data-controller="editor-js">
        <label class="form-label">{{ $label }}</label>
        <div data-editor-js-target="holder"></div>
        <input type="hidden" name="{{ $name }}"
            value='{{ old($name, $value ?? '{"time": ' . time() . ', "blocks": []}') }}' data-editor-js-target="input">
    </div>

    @vite(['resources/js/editor.js']) <!-- ✅ Подключаем сборку -->

    <script type="module">
        Orchid.register('editor-js', class extends Controller {
            static targets = ['holder', 'input'];

            connect() {
                console.log("Editor.js controller connected");

                let initialData;
                try {
                    const rawValue = this.inputTarget.value.trim();
                    initialData = rawValue ? JSON.parse(rawValue) : {
                        time: Date.now(),
                        blocks: []
                    };
                } catch (e) {
                    console.warn('Invalid JSON in editor field, resetting...', e);
                    initialData = {
                        time: Date.now(),
                        blocks: []
                    };
                }

                // ✅ Используем из window
                this.editor = new window.EditorJS({
                    holder: this.holderTarget,
                    tools: {
                        header: {
                            class: window.Tools.Header,
                            inlineToolbar: ['link']
                        },
                        paragraph: {
                            class: window.Tools.Paragraph
                        },
                        list: {
                            class: window.Tools.List,
                            inlineToolbar: true
                        },
                        checklist: {
                            class: window.Tools.Checklist
                        },
                        quote: {
                            class: window.Tools.Quote,
                            quotePlaceholder: 'Цитата',
                            captionPlaceholder: 'Автор'
                        },
                        embed: {
                            class: window.Tools.Embed
                        },
                        warning: {
                            class: window.Tools.Warning
                        },
                        code: {
                            class: window.Tools.Code
                        },
                        table: {
                            class: window.Tools.Table
                        },
                        image: {
                            class: window.Tools.Image,
                            config: {
                                endpoints: {
                                    byFile: '/upload/image'
                                }
                            }
                        }
                    },
                    data: initialData,

                    onChange: async () => {
                        const content = await this.editor.save();
                        this.inputTarget.value = JSON.stringify(content);
                    },

                    onReady: () => {
                        console.log("Editor.js is ready!");
                    },
                });
            }

            disconnect() {
                if (this.editor) {
                    this.editor.destroy();
                }
            }
        });
    </script>
@endcomponent