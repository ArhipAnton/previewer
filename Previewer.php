<?php declare(strict_types=1);

// Необходимо реализовать класс, функция которого делать превью строки.
// На вход мы подаем абзац текста, вы выходе получаем превью текста.
// Сначала отсекаем по кол-ву слов, затем ищем вхождение стоп слова(последнее), и если оно встречается, то обрезаем строку по нему, если нет, возвращаем превью обрезанное по кол-ву слов.
// Класс должен быть конфигурируемый, как минимум длинна обрезаемого текста и стоп слова(может быть несколько).
// В идеале покрыть тестами.


class Previewer
{
    private $originText;
    private $previewText;
    private $isReturnOrigin = true;

    public function __construct(string $text)
    {
        $this->originText = $text;
    }

    public function makePreview(int $words_count, string ...$stop_words): string
    {
        $this->previewText = $this->originText;
        $this->isReturnOrigin = true;

        if ($words_count < 1) {
            throw new InvalidArgumentException('"words_count" must be greater, than 1');
        }

        $words = $this->getWordsAsArray($words_count);
        $lastIndex = $this->getIndexOfLastWord($words, $stop_words);
        $this->cutText($lastIndex);

        return $this->previewText;
    }

    private function getWordsAsArray(int $words_count): array
    {
        $this->previewText .= ' ';
        $pattern = '/[^\w]*[\s]+[^\w]*/';
        $words = preg_split($pattern, $this->previewText, $words_count + 1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
        if (count($words) > $words_count) {
            $this->isReturnOrigin = false;
            unset($words[array_key_last($words)]);
        }

        return $words;
    }

    private function getIndexOfLastWord(array $words, array $stop_words): int
    {
        if (!empty($words)) {
            foreach (array_reverse($words, true) as $key => $originWord) {
                foreach ($stop_words as $searchWord) {
                    if (strcasecmp($originWord[0], trim($searchWord)) === 0) {
                        $this->isReturnOrigin = false;
                        return $originWord[1] + strlen($originWord[0]);
                    }
                }
            }
            $lastWord = end($words);
            return $lastWord[1] + strlen($lastWord[0]);
        }
        return 0;
    }

    private function cutText(int $lastIndex): void
    {
        if ($this->isReturnOrigin) {
            $this->previewText = $this->originText;
        } else {
            $this->previewText = substr($this->previewText, 0, $lastIndex);
        }
    }
}