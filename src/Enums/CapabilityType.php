<?php

namespace Skeleton\Store\Enums;

enum CapabilityType: string
{
    case PREMIUM_COURSES = 'access_premium_courses';
    case DIGITAL_RESOURCES = 'download_digital_resources';
    case PROJECT_TEMPLATES = 'project_templates';

    /**
     * Get a readable name for the capability
     */
    public function label(): string
    {
        return match ($this) {
            self::PREMIUM_COURSES => 'Premium Courses',
            self::DIGITAL_RESOURCES => 'Digital Resources',
            self::PROJECT_TEMPLATES => 'Project Templates'
        };
    }

    /**
     * Get a description for the capability
     */
    public function description(): string
    {
        return match ($this) {
            self::PREMIUM_COURSES => 'Access to premium video courses and training materials',
            self::DIGITAL_RESOURCES => 'Download ebooks, templates, and other digital resources',
            self::PROJECT_TEMPLATES => 'Ready-to-use project templates and starters'
        };
    }

    /**
     * Get all capabilities as array
     */
    public static function toArray(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
            ],
            self::cases()
        );
    }
}
