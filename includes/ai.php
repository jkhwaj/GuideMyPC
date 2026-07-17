<?php

declare(strict_types=1);

function ai_is_configured(): bool
{
    return config_value('AI_PROVIDER') !== null && config_value('AI_API_KEY') !== null && config_value('AI_API_KEY') !== '';
}

function ai_safe_disclosure(): string
{
    return 'GuideMyPC does not inspect your device remotely. Confirm important steps with reviewed sources and back up data before risky changes.';
}
