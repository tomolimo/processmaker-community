<?php

namespace ProcessMaker;

class Application extends \Illuminate\Foundation\Application
{
    /**
     * Overrides the path to the application "app" directory.
     *
     * @param string $path Optionally, a path to append to the app path
     * @return string
     */
    public function path($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'workflow' . DIRECTORY_SEPARATOR .
            'engine' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'ProcessMaker';
    }
}
