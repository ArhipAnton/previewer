<?php

class Previewer
{
    private int $wordCount;
    private array $stopWords;
    private string $postfix;
    private bool $needFormat = false;

    /**
     * @param int $wordCount
     * @param array $stopWords
     * @param bool $isOneLine
     * @param string $postfix
     * @throws Exception
     */
    public function __construct(int $wordCount, array $stopWords = [], bool $isOneLine = false, string $postfix = '')
    {
        if ($wordCount <= 0) {
            throw new InvalidArgumentException('аргумент $wordCount должен быть больше 0', 400);
        }
        if (!empty($stopWords)) {
            $notStringData = (array_filter($stopWords, function ($world) {
                return !is_string($world);
            }));
            if (!empty($notStringData)) {
                throw new InvalidArgumentException('аргумент $stopWords должен содержать массив строк', 400);
            }
        }

        $this->wordCount = $wordCount;
        $this->stopWords = $stopWords;
        $this->postfix = $postfix;
    }

    public function createPreview(string $text): string
    {
        $croppedText = $this->cropViaCount($text);
        $croppedText = $this->cropViaStopWorlds($croppedText);

        return $this->format($croppedText);
    }

    private function cropViaCount(string $text): string
    {
        $worlds = preg_split(
            "/[\s,]+/",
            $text,
            $this->wordCount + 1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE
        );
        if (count($worlds) < $this->wordCount + 1) {
            return $text;
        }
        $this->needFormat = true;

        $index = $worlds[array_key_last($worlds)][1];
        return substr($text, 0, $index);
    }

    private function cropViaStopWorlds(string $text): string
    {
        if (empty($this->stopWords)) {
            return $text;
        }

        $reverseStopWorlds = array_map(function ($string) {
            return quotemeta(strrev($string));
        }, $this->stopWords);
        $reverseText = strrev($text);
        $pattern = '/' . implode('|', $reverseStopWorlds) . '/';

        $foundWorlds = preg_match($pattern, $reverseText, $matches, PREG_OFFSET_CAPTURE);
        if (!$foundWorlds) {
            return $text;
        }
        $this->needFormat = true;

        $index = strlen($reverseText) - $matches[0][1];
        return substr($text, 0, $index);
    }

    private function format(string $text): string
    {
        if (!$this->needFormat) {
            return $text;
        }

        return trim($text) . $this->postfix;
    }
}
