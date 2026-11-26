<?php

declare(strict_types=1);

namespace Pollora\Metabox\Fields\Settings;

use LogicException;

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
        return $this->editorSetting('wpautop', $use);
    }

    /**
     * Set whether to display the media buttons.
     *
     * @param  bool  $show  Whether to display the media buttons.
     */
    public function mediaButtons(bool $show): static
    {
        return $this->editorSetting('media_buttons', $show);
    }

    /**
     * Set the default editor to use.
     *
     * @param  string  $editor  The default editor to use.
     */
    public function defaultEditor(string $editor): static
    {
        return $this->editorSetting('default_editor', $editor);
    }

    /**
     * Set whether to enable drag-and-drop upload.
     *
     * @param  bool  $enable  Whether to enable drag-and-drop upload.
     */
    public function dragDropUpload(bool $enable): static
    {
        return $this->editorSetting('drag_drop_upload', $enable);
    }

    /**
     * Set the name of the textarea.
     *
     * @param  string  $name  The name of the textarea.
     */
    public function textareaName(string $name): static
    {
        return $this->editorSetting('textarea_name', $name);
    }

    /**
     * Set the number of rows in the textarea.
     *
     * @param  int  $rows  The number of rows in the textarea.
     */
    public function textareaRows(int $rows): static
    {
        return $this->editorSetting('textarea_rows', $rows);
    }

    /**
     * Set the tabindex attribute for the textarea.
     *
     * @param  int  $index  The tabindex attribute for the textarea.
     */
    public function tabindex(int $index): static
    {
        return $this->editorSetting('tabindex', $index);
    }

    /**
     * Set the elements to focus when tabbing.
     *
     * @param  string  $elements  The elements to focus when tabbing.
     */
    public function tabfocusElements(string $elements): static
    {
        return $this->editorSetting('tabfocus_elements', $elements);
    }

    /**
     * Set the CSS for the editor.
     *
     * @param  string  $css  The CSS for the editor.
     */
    public function editorCss(string $css): static
    {
        return $this->editorSetting('editor_css', $css);
    }

    /**
     * Set the CSS class for the editor.
     *
     * @param  string  $class  The CSS class for the editor.
     */
    public function editorClass(string $class): static
    {
        return $this->editorSetting('editor_class', $class);
    }

    /**
     * Set whether to use the tinyMCE editor in teeny mode.
     *
     * @param  bool  $useTeeny  Whether to use the tinyMCE editor in teeny mode.
     */
    public function teeny(bool $useTeeny): static
    {
        return $this->editorSetting('teeny', $useTeeny);
    }

    /**
     * Set the configuration for the tinymce editor.
     *
     * @param  array<mixed>  $settings  The configuration for the tinymce editor.
     */
    public function tinymce(array $settings): static
    {
        return $this->editorSetting('tinymce', $settings);
    }

    /**
     * Set the configuration for the quicktags editor.
     *
     * @param  array<mixed>  $settings  The configuration for the quicktags editor.
     */
    public function quicktags(array $settings): static
    {
        return $this->editorSetting('quicktags', $settings);
    }

    /**
     * Enable the distraction-free writing mode (full screen) of the editor. Enabled by default.
     */
    public function distractionFreeWriting(bool $enabled = true): static
    {
        return $this->editorSetting('dfw', $enabled);
    }

    /**
     * Set one setting of the editor, kept with the other options of the field.
     *
     * @throws LogicException If the options were given as a string, which cannot hold a setting.
     */
    private function editorSetting(string $key, mixed $value): static
    {
        $options = $this->options ?? [];

        if (is_string($options)) {
            throw new LogicException("The editor setting '{$key}' cannot be added to options given as a string.");
        }

        $options[$key] = $value;
        $this->options = $options;

        return $this;
    }
}
