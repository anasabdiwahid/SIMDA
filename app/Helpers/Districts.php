<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Mogadishu districts, used by the District selects on the client registration and branch forms.
 */
final class Districts
{
    private const MOGADISHU = [
        'Abdiaziz', 'Boondheere', 'Darussalam', 'Deyniile', 'Dharkeynley', 'Garasbaaley', 'Gubadley',
        'Gubta', 'Heliwaa', 'Hodan', 'Howlwadaag', 'Kaaraan', 'Kaxda', 'Shangaani', 'Shibis',
        'Waaberi', 'Wadajir', 'Warta Nabadda', 'Xamar Jajab', 'Xamar Weyne', 'Yaaqshiid',
    ];

    /** @return list<string> */
    public static function mogadishu(): array
    {
        return self::MOGADISHU;
    }

    /**
     * <option> tags for a district select. A saved value that is not in the list (older records)
     * is kept as its own option so editing never loses it.
     */
    public static function options(string $selected = '', string $placeholder = '— Dooro degmada —'): string
    {
        $e = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $html = '<option value="">' . $e($placeholder) . '</option>';
        $list = self::MOGADISHU;
        if ($selected !== '' && !in_array($selected, $list, true)) {
            array_unshift($list, $selected);
        }
        foreach ($list as $d) {
            $html .= '<option value="' . $e($d) . '"' . ($d === $selected ? ' selected' : '') . '>' . $e($d) . '</option>';
        }
        return $html;
    }
}
