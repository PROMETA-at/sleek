@props(['method' => 'post', 'action' => '', 'preventUnload' => false])
@php
    if (! in_array(strtolower($method), ['get', 'post'])) {
        $formMethod = $method;
        $method = 'post';
    }
@endphp


@once
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('sleek__form', () => {
        let formState

        return ({
          loading: false,

          init() {
            formState = new FormState(this.$el)

            @if($preventUnload)
              window.addEventListener('beforeunload', (event) => {
                if (this.isDirty && !this.loading) event.preventDefault()
              })
            @endif
          },
          destroy() {
            formState = null
          },

          get isDirty() {
            return formState.isDirty
          },

          form: {
            ['@submit'](event) {
              // We're patching preventDefault here so users of this component can prevent form submission without
              // having to manually manage the loading state.
              const _preventDefault = event.preventDefault
              event.preventDefault = () => {
                this.loading = false
                _preventDefault.call(event)
              }

              this.loading = true
            }
          }
        });
      })
    })

    class FormState {
      #el
      #fieldMetadata = []
      #isDirty = false

      constructor(el) {
        this.#el = el
        this.initMetadata()
      }

      initMetadata() {
        this.#el.querySelectorAll('[name]').forEach(field => {
          this.#fieldMetadata.push(new FormFieldObserver(field))
          field.addEventListener('dirty', () => {
            if (!this.#isDirty) {
              this.#isDirty = true
              this.#el.dispatchEvent(new CustomEvent('dirty'));
            }
          })
          field.addEventListener('clean', () => {
            if (this.#isDirty && this.#fieldMetadata.every(field => !field.isDirty)) {
              this.#isDirty = false
              this.#el.dispatchEvent(new CustomEvent('clean'));
            }
          })
        })
      }

      get isDirty() {
        return this.#fieldMetadata.some(field => field.isDirty)
      }
    }

    class FormFieldObserver {
      #el
      #initialValue
      #isDirty = false

      constructor(el) {
        this.#el = el
        this.#initialValue = this.value

        this.#el.addEventListener('input', (e) => {
          if (!this.#isDirty && this.isDirty) {
            this.#isDirty = true
            this.#el.dispatchEvent(new CustomEvent('dirty'))
          } else if (this.#isDirty && !this.isDirty) {
            this.#isDirty = false
            this.#el.dispatchEvent(new CustomEvent('clean'))
          }
        })
      }

      get isDirty() {
        if (this.#el.hasAttribute('dirty') && !!this.#el.getAttribute('dirty'))
          return !!safeEval(this.#el.getAttribute('dirty'))

        return this.initialValue !== this.value
      }

      get value() {
        if (this.#el instanceof HTMLInputElement ||
                this.#el instanceof HTMLSelectElement ||
                this.#el instanceof HTMLTextAreaElement) {

          if (this.#el.type === 'checkbox' || this.#el.type === 'radio') {
            return this.#el.checked ?? false;
          } else {
            return this.#el.value;
          }
        } else {
          if (typeof this.#el.value !== 'undefined') {
            return this.#el.value;
          }
        }
      }

      get initialValue() {
        return this.#initialValue;
      }
    }

    function safeEval(jsString) {
        try {
            return eval(`(function() { return ${jsString}; })()`);
        } catch (e) {
            return jsString;
        }
    }
  </script>
@endonce

<form x-data="sleek__form" x-bind="form" method="{{ $method }}" action="{{ $action }}" {{ $attributes }}>
    @isset($formMethod) @method($formMethod) @endisset
    @if(strtolower($method) === 'post') @csrf @endif
    {{ $slot }}
</form>
