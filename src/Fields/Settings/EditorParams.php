<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

/**
 * Trait to configure settings for WordPress WYSIWYG editors explicitly.
 */
trait EditorParams
{
    use Options;

    /**
     * Set whether to automatically format paragraphs.
     *
     * @param  bool  $use  Whether to automatically format paragraphs.
     */
    public function wpautop(bool $use): static
    {
        $this->options['wpautop'] = $use;

        return $this;
    }

    /**
     * Set whether to display the media buttons.
     *
     * @param  bool  $show  Whether to display the media buttons.
     */
    public function mediaButtons(bool $show): static
    {
        $this->options['media_buttons'] = $show;

        return $this;
    }

    /**
     * Set the default editor to use.
     *
     * @param  string  $editor  The default editor to use.
     */
    public function defaultEditor(string $editor): static
    {
        $this->options['default_editor'] = $editor;

        return $this;
    }

    /**
     * Set whether to enable drag-and-drop upload.
     *
     * @param  bool  $enable  Whether to enable drag-and-drop upload.
     */
    public function dragDropUpload(bool $enable): static
    {
        $this->options['drag_drop_upload'] = $enable;

        return $this;
    }

    /**
     * Set the name of the textarea.
     *
     * @param  string  $name  The name of the textarea.
     */
    public function textareaName(string $name): static
    {
        $this->options['textarea_name'] = $name;

        return $this;
    }

    /**
     * Set the number of rows in the textarea.
     *
     * @param  int  $rows  The number of rows in the textarea.
     */
    public function textareaRows(int $rows): static
    {
        $this->options['textarea_rows'] = $rows;

        return $this;
    }

    /**
     * Set the tabindex attribute for the textarea.
     *
     * @param  int  $index  The tabindex attribute for the textarea.
     */
    public function tabindex(int $index): static
    {
        $this->options['tabindex'] = $index;

        return $this;
    }

    /**
     * Set the elements to focus when tabbing.
     *
     * @param  string  $elements  The elements to focus when tabbing.
     */
    public function tabfocusElements(string $elements): static
    {
        $this->options['tabfocus_elements'] = $elements;

        return $this;
    }

    /**
     * Set the CSS for the editor.
     *
     * @param  string  $css  The CSS for the editor.
     */
    public function editorCss(string $css): static
    {
        $this->options['editor_css'] = $css;

        return $this;
    }

    /**
     * Set the CSS class for the editor.
     *
     * @param  string  $class  The CSS class for the editor.
     */
    public function editorClass(string $class): static
    {
        $this->options['editor_class'] = $class;

        return $this;
    }

    /**
     * Set whether to use the tinyMCE editor in teeny mode.
     *
     * @param  bool  $useTeeny  Whether to use the tinyMCE editor in teeny mode.
     */
    public function teeny(bool $useTeeny): static
    {
        $this->options['teeny'] = $useTeeny;

        return $this;
    }

    /**
     * Set the configuration for the tinymce editor.
     *
     * @param  array<mixed>  $settings  The configuration for the tinymce editor.
     */
    public function tinymce(array $settings): static
    {
        $this->options['tinymce'] = $settings;

        return $this;
    }

    /**
     * Set the configuration for the quicktags editor.
     *
     * @param  array<mixed>  $settings  The configuration for the quicktags editor.
     */
    public function quicktags(array $settings): static
    {
        $this->options['quicktags'] = $settings;

        return $this;
    }

    /**
     * Enable the distraction-free writing mode (full screen) of the editor. Enabled by default.
     */
    public function distractionFreeWriting(bool $enabled = true): static
    {
        $this->options['dfw'] = $enabled;

        return $this;
    }
}
