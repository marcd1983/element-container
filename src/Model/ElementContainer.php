<?php

namespace Antlion\ElementContainer\Model;


use DNADesign\Elemental\Extensions\ElementalAreasExtension;
use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\NumericField;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\ToggleCompositeField;
use TractorCow\Colorpicker\Forms\ColorField;

/**
 * @property string $ContainerWidth
 * @property string $PaddingY
 * @property string $PaddingX
 * @property string $MarginY
 * @property string $BackgroundColor
 * @property string $OverlayColor
 * @property float  $OverlayOpacity
 * @property string $Theme
 */
class ElementContainer extends BaseElement
{
    private static string $table_name = 'ElementContainer';
    private static string $singular_name = 'Container';
    private static string $plural_name = 'Containers';
    private static string $description = 'A container block that can nest other blocks';
    private static string $icon = 'font-icon-block-globe-2';

    private static array $db = [
        // Width: contained (grid-container), full (full width), fluid (grid-container fluid)
        'ContainerWidth' => "Enum('contained,full,fluid','contained')",

        // Spacing options (keep simple + consistent)
        'PaddingY' => "Enum('none,small,medium,large','medium')",
        'PaddingX' => "Enum('none,small,medium,large','none')",
        'MarginY'  => "Enum('none,small,medium,large','none')",

        // Background options
        'BackgroundColor' => 'Varchar(20)', // hex like #ffffff
        'OverlayColor'    => 'Varchar(20)',
        'OverlayOpacity'  => 'Int', // 0–100

        // Text colour theme
        'Theme' => "Enum('light,dark','light')",
    ];

    private static array $has_one = [
        'BackgroundImage' => Image::class,
        'InnerElements'   => ElementalArea::class,
    ];

    private static array $owns = [
        'BackgroundImage',
        'InnerElements',
    ];

    private static array $cascade_deletes = [
        'InnerElements',
    ];

    private static array $cascade_duplicates = [
        'InnerElements',
    ];

    private static array $extensions = [
        ElementalAreasExtension::class,
    ];

    public function getType(): string
    {
        return 'Container';
    }

    public function getSummary(): string
    {
        $count = $this->InnerElements()?->Elements()?->Count() ?? 0;
        return $count ? "Contains {$count} nested block(s)" : 'Empty container';
    }

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        // Remove any auto-scaffolded fields so we can place them where we want
        $fields->removeByName([
            'ContainerWidth',
            'PaddingY',
            'PaddingX',
            'MarginY',
            'BackgroundImage',
            'BackgroundColor',
            'OverlayColor',
            'OverlayOpacity',
            'Theme',
        ]);

        $appearance = ToggleCompositeField::create(
            'ContainerAppearance',
            'Appearance',
            [
                DropdownField::create('Theme', 'Theme', [
                    'light' => 'Light',
                    'dark'  => 'Dark',
                ]),

                HeaderField::create('ContainerLayoutHeading', 'Layout', 3),
                OptionsetField::create('ContainerWidth', 'Container width', [
                    'contained' => 'Contained (fixed)',
                    'full'      => 'Full width',
                    'fluid'     => 'Fluid container',
                ]),
                DropdownField::create('PaddingY', 'Padding Y', $this->spacingOptions()),
                DropdownField::create('PaddingX', 'Padding X', $this->spacingOptions()),
                DropdownField::create('MarginY',  'Margin Y',  $this->spacingOptions()),

                HeaderField::create('ContainerBackgroundHeading', 'Background', 3),
                UploadField::create('BackgroundImage', 'Background image')
                    ->setFolderName('Uploads/Elements/Container')
                    ->setAllowedFileCategories('image'),
                ColorField::create('BackgroundColor', 'Background color'),

                HeaderField::create('ContainerOverlayHeading', 'Overlay', 3),
                ColorField::create('OverlayColor', 'Overlay color'),
                NumericField::create('OverlayOpacity', 'Overlay opacity (0–100)')
                    ->setDescription('e.g. 35 for 35% opacity'),
            ]
        )->setStartClosed(true);

        // Put it on the main tab (adjust placement as you like)
        $fields->addFieldToTab('Root.Main', $appearance);

        return $fields;
    }


    private function spacingOptions(): array
    {
        return [
            'none'   => 'None',
            'small'  => 'Small',
            'medium' => 'Medium',
            'large'  => 'Large',
        ];
    }

    /**
     * Used by ElementalAreasExtension to know which owned relation is the nested area.
     */
    public function getOwnedAreaRelationName(): string
    {
        return 'InnerElements';
    }

    public function inlineEditable(): bool
    {
        return false;
    }

    public function TextColor(): string
    {
        return $this->Theme === 'dark' ? '#fff' : '#111';
    }

    // --- Helpers for templates ---

    public function ContainerWidthClass(): string
    {
        return match ($this->ContainerWidth) {
            'contained' => 'contained',
            'fluid'     => 'fluid',
            'full'      => 'full',
            default     => 'contained',
        };
    }

    public function PaddingClasses(): string
    {
        // Map to your CSS system (Foundation-ish examples)
        $py = match ($this->PaddingY) {
            'none' => 'py-0', 'small' => 'py-1', 'medium' => 'py-2', 'large' => 'py-3', default => ''
        };
        $px = match ($this->PaddingX) {
            'none' => 'px-0', 'small' => 'px-1', 'medium' => 'px-2', 'large' => 'px-3', default => ''
        };
        return trim("{$py} {$px}");
    }

    public function MarginClasses(): string
    {
        $my = match ($this->MarginY) {
            'none' => 'my-0', 'small' => 'my-1', 'medium' => 'my-2', 'large' => 'my-3', default => ''
        };

        return trim("{$my}");
    }

    public function OverlayOpacityCss(): string
    {
        $pct = max(0, min(100, (int) $this->OverlayOpacity));
        return (string) round($pct / 100, 2);
    }

    public function HasOverlay(): bool
    {
        return (bool)$this->OverlayColor && (int)$this->OverlayOpacity > 0;
    }

    public function OverlayRGBA(): ?string
    {
        $hex = (string)$this->OverlayColor;
        $opacity = (int)$this->OverlayOpacity;

        if (!$hex || $opacity <= 0) {
            return null;
        }

        $rgb = $this->hexToRgb($hex);
        if (!$rgb) {
            return null;
        }

        return sprintf('rgba(%d,%d,%d,%.2f)', $rgb[0], $rgb[1], $rgb[2], round($opacity / 100, 2));
    }

    public function BackgroundRGBA(): ?string
    {
        // optional: if you ever want bg color with opacity too
        $hex = (string)$this->BackgroundColor;
        if (!$hex) {
            return null;
        }

        $rgb = $this->hexToRgb($hex);
        if (!$rgb) {
            return null;
        }

        return sprintf('rgb(%d,%d,%d)', $rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * Accepts "#fff", "fff", "#ffffff", "ffffff"
     * Returns [r,g,b] or null
     */
    private function hexToRgb(string $hex): ?array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = "{$hex[0]}{$hex[0]}{$hex[1]}{$hex[1]}{$hex[2]}{$hex[2]}";
        }

        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return null;
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

}