<?php

namespace App\Services;

use App\Models\Template;

class TemplateService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function render(string $templateName, array $data): string
    {
        $template = Template::where('name', $templateName)
            ->where('type', 'sms')
            ->firstOrFail();

        return $this->compile($template->content, $data);
    }

    protected function compile(string $content, array $data): string
    {
        return preg_replace_callback('/{{\s*(\w+)\s*}}/', 
            function ($matches) use ($data) {
                return $data[$matches[1]] ?? $matches[0];
            },
            $content
        );
    }
}
