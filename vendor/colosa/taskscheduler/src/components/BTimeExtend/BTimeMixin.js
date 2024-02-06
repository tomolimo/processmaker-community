
import { BFormTimepicker, BButton } from "bootstrap-vue";
import { BVFormBtnLabelControl } from "bootstrap-vue/src/utils/bv-form-btn-label-control";
import BTimeExtend from "./BTimeExtend";
import { isUndefinedOrNull } from "bootstrap-vue/src/utils/inspect";
let props = Object.assign({}, BFormTimepicker.props)
export default {
  name: "BTimeMixin",
  mixins: [BFormTimepicker],
  props,
  watch: {
    value(newVal) {
      this.localHMS = newVal || '';
    }
  },
  methods: {
    onInput(hms) {
      if (this.localHMS !== hms) {
        this.localHMS = hms
      }
    }
  },
  /**
   * Render BTimeMixin field extension
   * @param {*} h 
   */
  render(h) {
    const { localHMS, disabled, readonly } = this
    const placeholder = isUndefinedOrNull(this.placeholder)
      ? this.labelNoTimeSelected
      : this.placeholder

    // Footer buttons
    let $footer = []

    if (this.nowButton) {
      const label = this.labelNowButton
      $footer.push(
        h(
          BButton,
          {
            key: 'now-btn',
            props: { size: 'sm', disabled: disabled || readonly, variant: this.nowButtonVariant },
            attrs: { 'aria-label': label || null },
            on: { click: this.onNowButton }
          },
          label
        )
      )
    }

    if (this.resetButton) {
      if ($footer.length > 0) {
        // Add a "spacer" between buttons ('&nbsp;')
        $footer.push(h('span', '\u00a0'))
      }
      const label = this.labelResetButton
      $footer.push(
        h(
          BButton,
          {
            key: 'reset-btn',
            props: { size: 'sm', disabled: disabled || readonly, variant: this.resetButtonVariant },
            attrs: { 'aria-label': label || null },
            on: { click: this.onResetButton }
          },
          label
        )
      )
    }

    if (!this.noCloseButton) {
      if ($footer.length > 0) {
        // Add a "spacer" between buttons ('&nbsp;')
        $footer.push(h('span', '\u00a0'))
      }
      const label = this.labelCloseButton
      $footer.push(
        h(
          BButton,
          {
            key: 'close-btn',
            props: { size: 'sm', disabled, variant: this.closeButtonVariant },
            attrs: { 'aria-label': label || null },
            on: { click: this.onCloseButton }
          },
          label
        )
      )
    }

    if ($footer.length > 0) {
      $footer = [
        h(
          'div',
          {
            staticClass: 'b-form-date-controls d-flex flex-wrap',
            class: {
              'justify-content-between': $footer.length > 1,
              'justify-content-end': $footer.length < 2
            }
          },
          $footer
        )
      ]
    }

    const $time = h(
      BTimeExtend,
      {
        ref: 'time',
        staticClass: 'b-form-time-control',
        props: this.timeProps,
        on: {
          input: this.onInput,
          context: this.onContext
        }
      },
      $footer
    )

    return h(
      BVFormBtnLabelControl,
      {
        ref: 'control',
        staticClass: 'b-form-timepicker',
        props: {
          // This adds unneeded props, but reduces code size:
          ...this.$props,
          // Overridden / computed props
          id: this.safeId(),
          rtl: this.isRTL,
          lang: this.computedLang,
          value: localHMS || '',
          formattedValue: localHMS ? this.formattedValue : '',
          placeholder: placeholder || ''
        },
        on: {
          show: this.onShow,
          shown: this.onShown,
          hidden: this.onHidden
        },
        scopedSlots: {
          'button-content': this.$scopedSlots['button-content'] || this.defaultButtonFn
        }
      },
      [$time]
    )
  }
};
