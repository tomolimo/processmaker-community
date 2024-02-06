
import { BTime, BIconCircleFill, BFormSpinbutton } from "bootstrap-vue";
import identity from "bootstrap-vue/src/utils/identity";
let props = Object.assign({}, BTime.props)
export default {
    name: "BTimeExtend",
    mixins: [BTime],
    props,
    render(h) {
        /* istanbul ignore if */
        if (this.hidden) {
            // If hidden, we just render a placeholder comment
            return h()
        }

        const valueId = this.valueId
        const computedAriaLabelledby = this.computedAriaLabelledby
        const spinIds = []

        // Helper method to render a spinbutton
        const makeSpinbutton = (handler, key, classes, spinbuttonProps = {}) => {
            const id = this.safeId(`_spinbutton_${key}_`) || null
            spinIds.push(id)
            return h(BFormSpinbutton, {
                key: key,
                ref: 'spinners',
                refInFor: true,
                class: classes,
                props: {
                    id: id,
                    placeholder: '--',
                    vertical: true,
                    required: true,
                    disabled: this.disabled,
                    readonly: this.readonly,
                    locale: this.computedLocale,
                    labelIncrement: this.labelIncrement,
                    labelDecrement: this.labelDecrement,
                    wrap: true,
                    ariaControls: valueId,
                    min: 0,
                    ...spinbuttonProps
                },
                scopedSlots: this.spinScopedSlots,
                on: {
                    // We use `change` event to minimize SR verbosity
                    // As the spinbutton will announce each value change
                    // and we don't want the formatted time to be announced
                    // on each value input if repeat is happening
                    change: handler
                }
            })
        }

        // Helper method to return a "colon" separator
        const makeColon = () => {
            return h(
                'div',
                {
                    staticClass: 'd-flex flex-column',
                    class: {
                        'text-muted': this.disabled || this.readonly
                    },
                    attrs: { 'aria-hidden': 'true' }
                },
                [
                    h(BIconCircleFill, { props: { shiftV: 4, scale: 0.5 } }),
                    h(BIconCircleFill, { props: { shiftV: -4, scale: 0.5 } })
                ]
            )
        }

        let $spinners = []

        // Hours
        $spinners.push(
            makeSpinbutton(this.setHours, 'hours', 'b-time-hours', {
                value: this.modelHours,
                max: 23,
                step: 1,
                formatterFn: this.formatHours,
                ariaLabel: this.labelHours
            })
        )

        // Spacer
        $spinners.push(makeColon())

        // Minutes
        $spinners.push(
            makeSpinbutton(this.setMinutes, 'minutes', 'b-time-minutes', {
                value: this.modelMinutes,
                max: 59,
                disabled: true,
                step: this.minutesStep || 1,
                formatterFn: this.formatMinutes,
                ariaLabel: this.labelMinutes
            })
        )

        if (this.showSeconds) {
            // Spacer
            $spinners.push(makeColon())
            // Seconds
            $spinners.push(
                makeSpinbutton(this.setSeconds, 'seconds', 'b-time-seconds', {
                    value: this.modelSeconds,
                    max: 59,
                    step: this.secondsStep || 1,
                    formatterFn: this.formatSeconds,
                    ariaLabel: this.labelSeconds
                })
            )
        }

        // AM/PM ?
        if (this.is12Hour) {
            // TODO:
            //   If locale is RTL, unshift this instead of push?
            //   And switch class `ml-2` to `mr-2`
            //   Note some LTR locales (i.e. zh) also place AM/PM to the left
            $spinners.push(
                makeSpinbutton(this.setAmpm, 'ampm', 'b-time-ampm', {
                    value: this.modelAmpm,
                    max: 1,
                    formatterFn: this.formatAmpm,
                    ariaLabel: this.labelAmpm,
                    // We set `required` as `false`, since this always has a value
                    required: false
                })
            )
        }

        // Assemble spinners
        $spinners = h(
            'div',
            {
                staticClass: 'd-flex align-items-center justify-content-center mx-auto',
                attrs: {
                    role: 'group',
                    tabindex: this.disabled || this.readonly ? null : '-1',
                    'aria-labelledby': computedAriaLabelledby
                },
                on: {
                    keydown: this.onSpinLeftRight,
                    click /* istanbul ignore next */: evt => /* istanbul ignore next */ {
                        if (evt.target === evt.currentTarget) {
                            this.focus()
                        }
                    }
                }
            },
            $spinners
        )

        // Selected type display
        const $value = h(
            'output',
            {
                staticClass: 'form-control form-control-sm text-center',
                class: {
                    disabled: this.disabled || this.readonly
                },
                attrs: {
                    id: valueId,
                    role: 'status',
                    for: spinIds.filter(identity).join(' ') || null,
                    tabindex: this.disabled ? null : '-1',
                    'aria-live': this.isLive ? 'polite' : 'off',
                    'aria-atomic': 'true'
                },
                on: {
                    // Transfer focus/click to focus hours spinner
                    click: this.focus,
                    focus: this.focus
                }
            },
            [
                h('bdi', this.formattedTimeString),
                this.computedHMS ? h('span', { staticClass: 'sr-only' }, ` (${this.labelSelected}) `) : ''
            ]
        )
        const $header = h(
            'header',
            { staticClass: 'b-time-header', class: { 'sr-only': this.hideHeader } },
            [$value]
        )

        // Optional bottom slot
        let $slot = this.normalizeSlot('default')
        $slot = $slot ? h('footer', { staticClass: 'b-time-footer' }, $slot) : h()

        return h(
            'div',
            {
                staticClass: 'b-time d-inline-flex flex-column text-center',
                attrs: {
                    role: 'group',
                    lang: this.computedLang || null,
                    'aria-labelledby': computedAriaLabelledby || null,
                    'aria-disabled': this.disabled ? 'true' : null,
                    'aria-readonly': this.readonly && !this.disabled ? 'true' : null
                }
            },
            [$header, $spinners, $slot]
        )
    }
}