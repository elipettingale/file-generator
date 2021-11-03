<?php

namespace SmarterCoding\FileGenerator;

class Generator
{
    private $contents;
    private $syntax;

    public function __construct($template, $syntax = '\|\|')
    {
        $this->contents = file_get_contents($template);
        $this->syntax = $syntax;
    }

    public function getParameters()
    {
        $parameters = [];

        preg_match_all("/{$this->syntax}([^{$this->syntax}]*){$this->syntax}/", $this->contents, $matches);

        foreach ($matches[1] as $match) {
            $parameter = trim($match);

            if ($pos = strpos($parameter, ':')) {
                $parameter = substr($parameter, 0, $pos);
            }

            $parameters[] = $parameter;
        }

        return $parameters;
    }

    public function generate($target, $parameters)
    {
        preg_match_all("/{$this->syntax}([^{$this->syntax}]*){$this->syntax}/", $this->contents, $matches);

        foreach ($matches[1] as $index => $match) {
            $parameter = trim($match);
            $conversion = null;

            if ($pos = strpos($parameter, ':')) {
                $conversion = substr($parameter, $pos + 1);
                $parameter = substr($parameter, 0, $pos);
            }

            if ($conversion && function_exists($conversion)) {
                $value = $conversion($parameters[$parameter]);
            } else {
                $value = $parameters[$parameter];
            }

            $this->contents = str_replace($matches[0][$index], $value, $this->contents);
        }

        file_put_contents($target, $this->contents);
    }
}
