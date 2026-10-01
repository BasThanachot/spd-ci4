<?php

/**
 * Minimal polyfill สำหรับ class \Locale (ส่วนหนึ่งของ ext-intl)
 * ----------------------------------------------------------------------
 * XAMPP บน macOS บางรุ่น (เช่น 8.0.x) ไม่ได้แถม extension intl มา ทำให้
 * CodeIgniter 4 core ที่เรียกใช้ \Locale::setDefault()/getDefault() ตอน boot
 * เกิด Fatal: Class "Locale" not found
 *
 * ไฟล์นี้นิยาม \Locale เวอร์ชันย่อ "เฉพาะเมื่อยังไม่มี intl" เท่านั้น
 * จึงปลอดภัยเมื่อย้ายไปเครื่องที่มี intl จริง (จะไม่ถูกนิยามทับ)
 *
 * รองรับเมธอดที่ CI4 ใช้ในเส้นทางทำงานปกติ: setDefault, getDefault และ
 * เมธอดช่วยอื่น ๆ ที่คืนค่าปลอดภัยพอให้ระบบเดินต่อได้
 *
 * หมายเหตุ: ฟังก์ชันจัดรูปแบบที่พึ่ง ICU จริง (NumberFormatter/IntlDateFormatter/
 * MessageFormatter) ไม่ได้ถูก polyfill — CI4 มี guard ของตัวเองสำหรับส่วนเหล่านั้น
 * หากภายหลังติดตั้ง intl ได้ แนะนำให้ติดตั้งแล้วลบไฟล์นี้ออก
 */

if (! class_exists('Locale', false) && ! extension_loaded('intl')) {
    class Locale
    {
        /** @var string */
        protected static $default = 'en';

        public static function setDefault(string $locale): bool
        {
            self::$default = $locale !== '' ? $locale : 'en';

            return true;
        }

        public static function getDefault(): string
        {
            return self::$default;
        }

        public static function getPrimaryLanguage(?string $locale): ?string
        {
            $locale = $locale ?? self::$default;
            $parts  = preg_split('/[_-]/', $locale);

            return $parts[0] !== '' ? strtolower($parts[0]) : null;
        }

        public static function getRegion(?string $locale): ?string
        {
            $locale = $locale ?? self::$default;
            $parts  = preg_split('/[_-]/', $locale);

            return isset($parts[1]) ? strtoupper($parts[1]) : null;
        }

        public static function canonicalize(?string $locale): ?string
        {
            return $locale ?? self::$default;
        }

        /**
         * เลือก locale จาก HTTP Accept-Language แบบง่าย (คืนภาษาที่ q สูงสุด)
         */
        public static function acceptFromHttp(?string $header)
        {
            if (! $header) {
                return self::$default;
            }
            $best = null;
            $bestQ = -1.0;
            foreach (explode(',', $header) as $chunk) {
                $chunk = trim($chunk);
                if ($chunk === '') {
                    continue;
                }
                $q = 1.0;
                if (preg_match('/;\s*q\s*=\s*([0-9.]+)/i', $chunk, $m)) {
                    $q = (float) $m[1];
                    $chunk = trim(substr($chunk, 0, strpos($chunk, ';')));
                }
                if ($q > $bestQ && $chunk !== '') {
                    $bestQ = $q;
                    $best  = str_replace('-', '_', $chunk);
                }
            }

            return $best ?: self::$default;
        }

        /**
         * เมธอดอื่น ๆ ที่อาจถูกเรียก — คืนค่าปลอดภัยแบบกลาง ๆ
         */
        public static function __callStatic($name, $args)
        {
            return self::$default;
        }
    }
}
