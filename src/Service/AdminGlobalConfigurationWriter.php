<?php

namespace OpenDemat\AdminBundle\Service;

final class AdminGlobalConfigurationWriter
{
    private const BLOCK_START = '###> open-demat-admin ###';
    private const BLOCK_END = '###< open-demat-admin ###';

    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    /**
     * @param array<string, string> $values
     */
    public function save(array $values): void
    {
        $path = $this->projectDir . '/.env.local';
        $content = is_file($path) ? (string) file_get_contents($path) : '';
        $block = $this->buildBlock($values);

        $pattern = '/\n?' . preg_quote(self::BLOCK_START, '/') . '.*?' . preg_quote(self::BLOCK_END, '/') . '\n?/s';

        if (preg_match($pattern, $content) === 1) {
            $content = preg_replace($pattern, "\n" . $block . "\n", $content) ?? $content;
        } else {
            $content = rtrim($content) . "\n\n" . $block . "\n";
        }

        if (file_put_contents($path, ltrim($content), LOCK_EX) === false) {
            throw new \RuntimeException(sprintf('Impossible d écrire la configuration dans %s.', $path));
        }
    }

    /**
     * @param array<string, string> $values
     */
    private function buildBlock(array $values): string
    {
        $lines = [self::BLOCK_START, '# Bloc gere depuis Administration > Configuration globale.'];

        foreach ($values as $name => $value) {
            $lines[] = sprintf('%s="%s"', $name, $this->escapeEnvValue($value));
        }

        $lines[] = self::BLOCK_END;

        return implode("\n", $lines);
    }

    private function escapeEnvValue(string $value): string
    {
        return str_replace(
            ["\\", '"', "\n", "\r"],
            ["\\\\", '\"', '\n', ''],
            $value
        );
    }
}
