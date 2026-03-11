@component($typeForm, get_defined_vars())
    <div data-controller="dynamic-field">
        <input
            {{
                $attributes->merge([
                    'data-dynamic-field-target' => 'name',
                    'data-action' => 'input->dynamic-field#greet',
                ])
            }}>

        <span data-dynamic-field-target="output"></span>
    </div>
@endcomponent

<script>
    Orchid.register('dynamic-field', class extends Controller {
        static targets = ['name', 'output'];

        connect() {
            console.log("MyInput controller has been connected!");
        }

        greet() {
            this.outputTarget.textContent =
                `Hello, ${this.nameTarget.value}!`;
        }
    });
</script>
