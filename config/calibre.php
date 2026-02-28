<?php

return [
    /*
    |--------------------------------------------------------------------------
    | مسار أمر ebook-convert (Calibre)
    |--------------------------------------------------------------------------
    |
    | لتحويل EPUB إلى PDF تلقائياً بعد الرفع.
    | - ويندوز: 'C:\Program Files\calibre2\ebook-convert.exe' أو اترك null إذا أضفته لـ PATH
    | - لينكس/ماك: عادة 'ebook-convert' (بعد تثبيت Calibre)
    |
    */
    'ebook_convert_path' => env('CALIBRE_EBOOK_CONVERT', 'ebook-convert'),
];
