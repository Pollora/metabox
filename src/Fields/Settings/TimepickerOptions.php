<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

/**
 * Trait for configuring jQuery UI Timepicker options.
 */
trait TimepickerOptions
{
    use JsOptions;

    /**
     * Show or hide the button panel in the jQuery UI Timepicker.
     *
     * @param  bool  $show  Whether to show the button panel.
     */
    public function showButtonPanel(bool $show = true): static
    {
        $this->js_options['showButtonPanel'] = $show;

        return $this;
    }

    /**
     * Enable or disable the time-only mode in the jQuery UI Timepicker.
     *
     * @param  bool  $timeOnly  Whether to enable time-only mode.
     */
    public function timeOnly(bool $timeOnly = true): static
    {
        $this->js_options['timeOnly'] = $timeOnly;

        return $this;
    }

    /**
     * Show or hide the date when using time-only mode in the jQuery UI Timepicker.
     *
     * @param  bool  $showDate  Whether to show the date when using time-only mode.
     */
    public function timeOnlyShowDate(bool $showDate = true): static
    {
        $this->js_options['timeOnlyShowDate'] = $showDate;

        return $this;
    }

    /**
     * Set the step size for hours in the jQuery UI Timepicker.
     *
     * @param  int  $step  The step size for hours.
     */
    public function stepHour(int $step): static
    {
        $this->js_options['stepHour'] = $step;

        return $this;
    }

    /**
     * Set the step size for minutes in the jQuery UI Timepicker.
     *
     * @param  int  $step  The step size for minutes.
     */
    public function stepMinute(int $step): static
    {
        $this->js_options['stepMinute'] = $step;

        return $this;
    }

    /**
     * Set the step size for seconds in the jQuery UI Timepicker.
     *
     * @param  int  $step  The step size for seconds.
     */
    public function stepSecond(int $step): static
    {
        $this->js_options['stepSecond'] = $step;

        return $this;
    }

    /**
     * Set the step size for milliseconds in the jQuery UI Timepicker.
     *
     * @param  int  $step  The step size for milliseconds.
     */
    public function stepMillisec(int $step): static
    {
        $this->js_options['stepMillisec'] = $step;

        return $this;
    }

    /**
     * Set the step size for microseconds in the jQuery UI Timepicker.
     *
     * @param  int  $step  The step size for microseconds.
     */
    public function stepMicrosec(int $step): static
    {
        $this->js_options['stepMicrosec'] = $step;

        return $this;
    }

    /**
     * Set the hour value in the jQuery UI Timepicker.
     *
     * @param  int  $hour  The hour value.
     */
    public function hour(int $hour): static
    {
        $this->js_options['hour'] = $hour;

        return $this;
    }

    /**
     * Set the minute value in the jQuery UI Timepicker.
     *
     * @param  int  $minute  The minute value.
     */
    public function minute(int $minute): static
    {
        $this->js_options['minute'] = $minute;

        return $this;
    }

    /**
     * Set the second value in the jQuery UI Timepicker.
     *
     * @param  int  $second  The second value.
     */
    public function second(int $second): static
    {
        $this->js_options['second'] = $second;

        return $this;
    }

    /**
     * Set the millisecond value in the jQuery UI Timepicker.
     *
     * @param  int  $millisec  The millisecond value.
     */
    public function millisec(int $millisec): static
    {
        $this->js_options['millisec'] = $millisec;

        return $this;
    }

    /**
     * Set the microsecond value in the jQuery UI Timepicker.
     *
     * @param  int  $microsec  The microsecond value.
     */
    public function microsec(int $microsec): static
    {
        $this->js_options['microsec'] = $microsec;

        return $this;
    }

    /**
     * Set the timezone value in the jQuery UI Timepicker.
     *
     * @param  int  $timezone  The timezone value.
     */
    public function timezone(int $timezone): static
    {
        $this->js_options['timezone'] = $timezone;

        return $this;
    }

    /**
     * Set the control type for the jQuery UI Timepicker.
     *
     * @param  string  $type  The control type.
     */
    public function controlType(string $type): static
    {
        $this->js_options['controlType'] = $type;

        return $this;
    }

    /**
     * Set the one-line mode for the jQuery UI Timepicker.
     *
     * @param  bool  $oneLine  Whether to enable one-line mode.
     */
    public function oneLine(bool $oneLine = true): static
    {
        $this->js_options['oneLine'] = $oneLine;

        return $this;
    }

    /**
     * Set the current text value in the jQuery UI Timepicker.
     *
     * @param  string  $text  The current text value.
     */
    public function currentText(string $text): static
    {
        $this->js_options['currentText'] = $text;

        return $this;
    }

    /**
     * Set the close text value in the jQuery UI Timepicker.
     *
     * @param  string  $text  The close text value.
     */
    public function closeText(string $text): static
    {
        $this->js_options['closeText'] = $text;

        return $this;
    }

    /**
     * Set the AM names for the jQuery UI Timepicker.
     *
     * @param  array<mixed>  $names  The AM names.
     */
    public function amNames(array $names): static
    {
        $this->js_options['amNames'] = $names;

        return $this;
    }

    /**
     * Set the PM names for the jQuery UI Timepicker.
     *
     * @param  array<mixed>  $names  The PM names.
     */
    public function pmNames(array $names): static
    {
        $this->js_options['pmNames'] = $names;

        return $this;
    }

    /**
     * Set the time format for the jQuery UI Timepicker.
     *
     * @param  string  $format  The time format.
     */
    public function timeFormat(string $format): static
    {
        $this->js_options['timeFormat'] = $format;

        return $this;
    }

    /**
     * Set the time suffix for the jQuery UI Timepicker.
     *
     * @param  string  $suffix  The time suffix.
     */
    public function timeSuffix(string $suffix): static
    {
        $this->js_options['timeSuffix'] = $suffix;

        return $this;
    }

    /**
     * Set the title of the time-only picker.
     *
     * @param  string  $title  The title to be displayed.
     */
    public function timeOnlyTitle(string $title): static
    {
        $this->js_options['timeOnlyTitle'] = $title;

        return $this;
    }

    /**
     * Set the text for the time display.
     *
     * @param  string  $text  The text to be displayed.
     */
    public function timeText(string $text): static
    {
        $this->js_options['timeText'] = $text;

        return $this;
    }

    /**
     * Set the text for the hour display.
     *
     * @param  string  $text  The text to be displayed.
     */
    public function hourText(string $text): static
    {
        $this->js_options['hourText'] = $text;

        return $this;
    }

    /**
     * Set the text for the minute display.
     *
     * @param  string  $text  The text to be displayed.
     */
    public function minuteText(string $text): static
    {
        $this->js_options['minuteText'] = $text;

        return $this;
    }

    /**
     * Set the text for the second display.
     *
     * @param  string  $text  The text to be displayed.
     */
    public function secondText(string $text): static
    {
        $this->js_options['secondText'] = $text;

        return $this;
    }

    /**
     * Set the text for the milliseconds display.
     *
     * @param  string  $text  The text to be displayed.
     */
    public function millisecText(string $text): static
    {
        $this->js_options['millisecText'] = $text;

        return $this;
    }

    /**
     * Set the text for the microseconds display.
     *
     * @param  string  $text  The text to be displayed.
     */
    public function microsecText(string $text): static
    {
        $this->js_options['microsecText'] = $text;

        return $this;
    }

    /**
     * Set the text for the timezone display.
     *
     * @param  string  $text  The text to be displayed.
     */
    public function timezoneText(string $text): static
    {
        $this->js_options['timezoneText'] = $text;

        return $this;
    }

    /**
     * Set whether the timepicker should be displayed in RTL mode.
     *
     * @param  bool  $isRTL  Whether the timepicker should be displayed in RTL mode.
     */
    public function isRTL(bool $isRTL = true): static
    {
        $this->js_options['isRTL'] = $isRTL;

        return $this;
    }

    /**
     * Set the grid for the hour display.
     *
     * @param  int  $grid  The grid value.
     */
    public function hourGrid(int $grid): static
    {
        $this->js_options['hourGrid'] = $grid;

        return $this;
    }

    /**
     * Set the grid for the minute display.
     *
     * @param  int  $grid  The grid value.
     */
    public function minuteGrid(int $grid): static
    {
        $this->js_options['minuteGrid'] = $grid;

        return $this;
    }

    /**
     * Set the grid for the second display.
     *
     * @param  int  $grid  The grid value.
     */
    public function secondGrid(int $grid): static
    {
        $this->js_options['secondGrid'] = $grid;

        return $this;
    }

    /**
     * Set the grid for the milliseconds display.
     *
     * @param  int  $grid  The grid value.
     */
    public function millisecGrid(int $grid): static
    {
        $this->js_options['millisecGrid'] = $grid;

        return $this;
    }

    /**
     * Set the grid for the microseconds display.
     *
     * @param  int  $grid  The grid value.
     */
    public function microsecGrid(int $grid): static
    {
        $this->js_options['microsecGrid'] = $grid;

        return $this;
    }

    /**
     * Set whether the hour display should be shown.
     *
     * @param  bool  $show  Whether the hour display should be shown.
     */
    public function showHour(bool $show = true): static
    {
        $this->js_options['showHour'] = $show;

        return $this;
    }

    /**
     * Set whether the minute display should be shown.
     *
     * @param  bool  $show  Whether the minute display should be shown.
     */
    public function showMinute(bool $show = true): static
    {
        $this->js_options['showMinute'] = $show;

        return $this;
    }

    /**
     * Set whether the second display should be shown.
     *
     * @param  bool  $show  Whether the second display should be shown.
     */
    public function showSecond(bool $show = true): static
    {
        $this->js_options['showSecond'] = $show;

        return $this;
    }

    /**
     * Set whether the milliseconds display should be shown.
     *
     * @param  bool  $show  Whether the milliseconds display should be shown.
     */
    public function showMillisec(bool $show = true): static
    {
        $this->js_options['showMillisec'] = $show;

        return $this;
    }

    /**
     * Set whether the microseconds display should be shown.
     *
     * @param  bool  $show  Whether the microseconds display should be shown.
     */
    public function showMicrosec(bool $show = true): static
    {
        $this->js_options['showMicrosec'] = $show;

        return $this;
    }

    /**
     * Set whether the timezone display should be shown.
     *
     * @param  bool  $show  Whether the timezone display should be shown.
     */
    public function showTimezone(bool $show = true): static
    {
        $this->js_options['showTimezone'] = $show;

        return $this;
    }

    /**
     * Set the minimum hour value.
     *
     * @param  int  $hourMin  The minimum hour value.
     */
    public function hourMin(int $hourMin): static
    {
        $this->js_options['hourMin'] = $hourMin;

        return $this;
    }

    /**
     * Set the minimum minute value.
     *
     * @param  int  $minuteMin  The minimum minute value.
     */
    public function minuteMin(int $minuteMin): static
    {
        $this->js_options['minuteMin'] = $minuteMin;

        return $this;
    }

    /**
     * Set the minimum second value.
     *
     * @param  int  $secondMin  The minimum second value.
     */
    public function secondMin(int $secondMin): static
    {
        $this->js_options['secondMin'] = $secondMin;

        return $this;
    }

    /**
     * Set the minimum number of milliseconds allowed.
     *
     * @param  int  $millisecMin  The minimum number of milliseconds.
     */
    public function millisecMin(int $millisecMin): static
    {
        $this->js_options['millisecMin'] = $millisecMin;

        return $this;
    }

    /**
     * Set the minimum number of microseconds allowed.
     *
     * @param  int  $microsecMin  The minimum number of microseconds.
     */
    public function microsecMin(int $microsecMin): static
    {
        $this->js_options['microsecMin'] = $microsecMin;

        return $this;
    }

    /**
     * Set the maximum hour value allowed.
     *
     * @param  int  $hourMax  The maximum hour value.
     */
    public function hourMax(int $hourMax): static
    {
        $this->js_options['hourMax'] = $hourMax;

        return $this;
    }

    /**
     * Set the maximum minute value allowed.
     *
     * @param  int  $minuteMax  The maximum minute value.
     */
    public function minuteMax(int $minuteMax): static
    {
        $this->js_options['minuteMax'] = $minuteMax;

        return $this;
    }

    /**
     * Set the maximum second value allowed.
     *
     * @param  int  $secondMax  The maximum second value.
     */
    public function secondMax(int $secondMax): static
    {
        $this->js_options['secondMax'] = $secondMax;

        return $this;
    }

    /**
     * Set the maximum number of milliseconds allowed.
     *
     * @param  int  $millisecMax  The maximum number of milliseconds.
     */
    public function millisecMax(int $millisecMax): static
    {
        $this->js_options['millisecMax'] = $millisecMax;

        return $this;
    }

    /**
     * Set the maximum number of microseconds allowed.
     *
     * @param  int  $microsecMax  The maximum number of microseconds.
     */
    public function microsecMax(int $microsecMax): static
    {
        $this->js_options['microsecMax'] = $microsecMax;

        return $this;
    }

    /**
     * Set the minimum date-time value allowed.
     *
     * @param  string  $minDateTime  The minimum date-time value.
     */
    public function minDateTime(string $minDateTime): static
    {
        $this->js_options['minDateTime'] = $minDateTime;

        return $this;
    }

    /**
     * Set the maximum date-time value allowed.
     *
     * @param  string  $maxDateTime  The maximum date-time value.
     */
    public function maxDateTime(string $maxDateTime): static
    {
        $this->js_options['maxDateTime'] = $maxDateTime;

        return $this;
    }

    /**
     * Set the minimum time value allowed.
     *
     * @param  string  $minTime  The minimum time value.
     */
    public function minTime(string $minTime): static
    {
        $this->js_options['minTime'] = $minTime;

        return $this;
    }

    /**
     * Set the maximum time value allowed.
     *
     * @param  string  $maxTime  The maximum time value.
     */
    public function maxTime(string $maxTime): static
    {
        $this->js_options['maxTime'] = $maxTime;

        return $this;
    }

    /**
     * Set whether the timepicker should always set the time.
     *
     * @param  bool  $alwaysSetTime  Whether the timepicker should always set the time.
     */
    public function alwaysSetTime(bool $alwaysSetTime = true): static
    {
        $this->js_options['alwaysSetTime'] = $alwaysSetTime;

        return $this;
    }

    /**
     * Set whether the timepicker should have a slider access.
     *
     * @param  bool  $addSliderAccess  Whether the timepicker should have a slider access.
     */
    public function addSliderAccess(bool $addSliderAccess = true): static
    {
        $this->js_options['addSliderAccess'] = $addSliderAccess;

        return $this;
    }

    /**
     * Set the arguments for the slider access.
     *
     * @param  array<mixed>  $args  The arguments for the slider access.
     */
    public function sliderAccessArgs(array $args): static
    {
        $this->js_options['sliderAccessArgs'] = $args;

        return $this;
    }

    /**
     * Set whether the timepicker should be shown.
     *
     * @param  bool  $show  Whether the timepicker should be shown.
     */
    public function showTimepicker(bool $show = true): static
    {
        $this->js_options['showTimepicker'] = $show;

        return $this;
    }

    /**
     * Set the default time value.
     *
     * @param  string  $defaultValue  The default time value.
     */
    public function defaultTimeValue(string $defaultValue): static
    {
        $this->js_options['defaultValue'] = $defaultValue;

        return $this;
    }

    /**
     * Set the parse format for the time value.
     *
     * @param  string  $parse  The parse format for the time value.
     */
    public function parse(string $parse): static
    {
        $this->js_options['parse'] = $parse;

        return $this;
    }
}
