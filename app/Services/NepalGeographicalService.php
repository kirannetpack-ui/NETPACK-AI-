<?php

namespace App\Services;

class NepalGeographicalService
{
    /**
     * Canonical list of Nepal's 7 Provinces and 77 Districts.
     *
     * @var array<string, array{code: string, alias: string, capital: string, districts: array<string>}>
     */
    public const PROVINCES = [
        'Koshi Province' => [
            'code' => 'P1',
            'alias' => 'Province No. 1',
            'capital' => 'Biratnagar',
            'districts' => [
                'Bhojpur',
                'Dhankuta',
                'Ilam',
                'Jhapa',
                'Khotang',
                'Morang',
                'Okhaldhunga',
                'Panchthar',
                'Sankhuwasabha',
                'Solukhumbu',
                'Sunsari',
                'Taplejung',
                'Terhathum',
                'Udayapur',
            ],
        ],
        'Madhesh Province' => [
            'code' => 'P2',
            'alias' => 'Province No. 2',
            'capital' => 'Janakpur',
            'districts' => [
                'Bara',
                'Dhanusha',
                'Mahottari',
                'Parsa',
                'Rautahat',
                'Saptari',
                'Sarlahi',
                'Siraha',
            ],
        ],
        'Bagmati Province' => [
            'code' => 'P3',
            'alias' => 'Province No. 3',
            'capital' => 'Hetauda',
            'districts' => [
                'Bhaktapur',
                'Chitwan',
                'Dhading',
                'Dolakha',
                'Kathmandu',
                'Kavrepalanchok',
                'Lalitpur',
                'Makwanpur',
                'Nuwakot',
                'Ramechhap',
                'Rasuwa',
                'Sindhuli',
                'Sindhupalchok',
            ],
        ],
        'Gandaki Province' => [
            'code' => 'P4',
            'alias' => 'Province No. 4',
            'capital' => 'Pokhara',
            'districts' => [
                'Baglung',
                'Gorkha',
                'Kaski',
                'Lamjung',
                'Manang',
                'Mustang',
                'Myagdi',
                'Nawalpur',
                'Parbat',
                'Syangja',
                'Tanahun',
            ],
        ],
        'Lumbini Province' => [
            'code' => 'P5',
            'alias' => 'Province No. 5',
            'capital' => 'Deukhuri (Dang)',
            'districts' => [
                'Arghakhanchi',
                'Banke',
                'Bardiya',
                'Dang',
                'Eastern Rukum',
                'Gulmi',
                'Kapilvastu',
                'Palpa',
                'Parasi',
                'Pyuthan',
                'Rolpa',
                'Rupandehi',
            ],
        ],
        'Karnali Province' => [
            'code' => 'P6',
            'alias' => 'Province No. 6',
            'capital' => 'Birendranagar',
            'districts' => [
                'Dailekh',
                'Dolpa',
                'Humla',
                'Jajarkot',
                'Jumla',
                'Kalikot',
                'Mugu',
                'Salyan',
                'Surkhet',
                'Western Rukum',
            ],
        ],
        'Sudurpashchim Province' => [
            'code' => 'P7',
            'alias' => 'Province No. 7',
            'capital' => 'Godawari',
            'districts' => [
                'Achham',
                'Baitadi',
                'Bajhang',
                'Bajura',
                'Dadeldhura',
                'Darchula',
                'Doti',
                'Kailali',
                'Kanchanpur',
            ],
        ],
    ];

    /**
     * Official constitutional district headquarters / capitals for all 77 districts of Nepal.
     * Every district depot is stationed at its district capital.
     *
     * @var array<string, string>
     */
    public const DISTRICT_CAPITALS = [
        // Koshi Province (14)
        'Bhojpur' => 'Bhojpur',
        'Dhankuta' => 'Dhankuta',
        'Ilam' => 'Ilam',
        'Jhapa' => 'Bhadrapur',
        'Khotang' => 'Diktel',
        'Morang' => 'Biratnagar',
        'Okhaldhunga' => 'Siddhicharan (Okhaldhunga)',
        'Panchthar' => 'Phidim',
        'Sankhuwasabha' => 'Khandbari',
        'Solukhumbu' => 'Salleri',
        'Sunsari' => 'Inaruwa',
        'Taplejung' => 'Fungling (Taplejung)',
        'Terhathum' => 'Myanglung',
        'Udayapur' => 'Gaighat (Triyuga)',

        // Madhesh Province (8)
        'Bara' => 'Kalaiya',
        'Dhanusha' => 'Janakpur',
        'Mahottari' => 'Jaleshwar',
        'Parsa' => 'Birgunj',
        'Rautahat' => 'Gaur',
        'Saptari' => 'Rajbiraj',
        'Sarlahi' => 'Malangwa',
        'Siraha' => 'Siraha',

        // Bagmati Province (13)
        'Bhaktapur' => 'Bhaktapur',
        'Chitwan' => 'Bharatpur',
        'Dhading' => 'Dhading Besi (Nilkantha)',
        'Dolakha' => 'Charikot (Bhimeshwar)',
        'Kathmandu' => 'Kathmandu',
        'Kavrepalanchok' => 'Dhulikhel',
        'Lalitpur' => 'Patan (Lalitpur)',
        'Makwanpur' => 'Hetauda',
        'Nuwakot' => 'Bidur',
        'Ramechhap' => 'Manthali',
        'Rasuwa' => 'Dhunche',
        'Sindhuli' => 'Kamalamai (Sindhulimadi)',
        'Sindhupalchok' => 'Chautara',

        // Gandaki Province (11)
        'Baglung' => 'Baglung',
        'Gorkha' => 'Gorkha',
        'Kaski' => 'Pokhara',
        'Lamjung' => 'Besisahar',
        'Manang' => 'Chame',
        'Mustang' => 'Jomsom',
        'Myagdi' => 'Beni',
        'Nawalpur' => 'Kawasoti',
        'Parbat' => 'Kusma',
        'Syangja' => 'Putalibazar (Syangja)',
        'Tanahun' => 'Damauli (Vyas)',

        // Lumbini Province (12)
        'Arghakhanchi' => 'Sandhikharka',
        'Banke' => 'Nepalgunj',
        'Bardiya' => 'Gulariya',
        'Dang' => 'Ghorahi',
        'Eastern Rukum' => 'Rukumkot',
        'Rukum East' => 'Rukumkot',
        'Gulmi' => 'Tamghas (Resunga)',
        'Kapilvastu' => 'Taulihawa (Kapilvastu)',
        'Palpa' => 'Tansen',
        'Parasi' => 'Ramgram (Parasi)',
        'Pyuthan' => 'Pyuthan',
        'Rolpa' => 'Liwang',
        'Rupandehi' => 'Siddharthanagar (Bhairahawa)',

        // Karnali Province (10)
        'Dailekh' => 'Narayan (Dailekh)',
        'Dolpa' => 'Dunai',
        'Humla' => 'Simikot',
        'Jajarkot' => 'Khalanga (Bheri)',
        'Jumla' => 'Chandannath (Jumla)',
        'Kalikot' => 'Manma',
        'Mugu' => 'Gamgadhi',
        'Salyan' => 'Khalanga (Sharada)',
        'Surkhet' => 'Birendranagar',
        'Western Rukum' => 'Musikot',
        'Rukum West' => 'Musikot',

        // Sudurpashchim Province (9)
        'Achham' => 'Mangalsen',
        'Baitadi' => 'Dasharathchand (Baitadi)',
        'Bajhang' => 'Chainpur (Jayaprithvi)',
        'Bajura' => 'Martadi (Badimalika)',
        'Dadeldhura' => 'Amargadhi (Dadeldhura)',
        'Darchula' => 'Khalanga (Darchula)',
        'Doti' => 'Dipayal Silgadhi',
        'Kailali' => 'Dhangadhi',
        'Kanchanpur' => 'Bhimdatta (Mahendranagar)',
    ];

    /**
     * Get all 7 provinces with metadata.
     */
    public static function getProvinces(): array
    {
        return self::PROVINCES;
    }

    /**
     * Get province names as a flat array.
     */
    public static function getProvinceNames(): array
    {
        return array_keys(self::PROVINCES);
    }

    /**
     * Get all 77 districts alphabetically sorted.
     */
    public static function getAllDistricts(): array
    {
        $districts = [];
        foreach (self::PROVINCES as $p) {
            foreach ($p['districts'] as $d) {
                $districts[] = $d;
            }
        }
        sort($districts);
        return array_values(array_unique($districts));
    }

    /**
     * Get array mapping Province Name => array of districts.
     */
    public static function getProvinceDistrictMap(): array
    {
        $map = [];
        foreach (self::PROVINCES as $pName => $pData) {
            $map[$pName] = $pData['districts'];
        }
        return $map;
    }

    /**
     * Get districts strictly under a given province name, code, or alias.
     */
    public static function getDistrictsByProvince(?string $province): array
    {
        if (empty($province)) {
            return [];
        }

        $provinceTrimmed = trim($province);

        foreach (self::PROVINCES as $name => $data) {
            if (
                strcasecmp($name, $provinceTrimmed) === 0 ||
                strcasecmp($data['code'], $provinceTrimmed) === 0 ||
                (isset($data['alias']) && strcasecmp($data['alias'], $provinceTrimmed) === 0) ||
                stripos($provinceTrimmed, explode(' ', $name)[0]) !== false
            ) {
                return $data['districts'];
            }
        }

        return [];
    }

    /**
     * Lookup province for a district.
     */
    public static function getProvinceForDistrict(?string $district): ?string
    {
        if (empty($district)) {
            return null;
        }

        $districtTrimmed = trim($district);

        foreach (self::PROVINCES as $pName => $pData) {
            foreach ($pData['districts'] as $d) {
                if (strcasecmp($d, $districtTrimmed) === 0) {
                    return $pName;
                }
            }
        }

        return null;
    }

    /**
     * Return JSON string for easy inline script consumption.
     */
    public static function getProvincesJson(): string
    {
        return json_encode(self::getProvinceDistrictMap(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

    /**
     * Get constitutional capital / headquarters for a given district.
     */
    public static function getDistrictCapital(?string $district): string
    {
        if (empty($district)) {
            return '';
        }
        $dTrimmed = trim($district);
        foreach (self::DISTRICT_CAPITALS as $name => $capital) {
            if (strcasecmp($name, $dTrimmed) === 0) {
                return $capital;
            }
        }
        return $dTrimmed;
    }

    /**
     * Get official district regional depot title with its capital location.
     */
    public static function getDepotNameForDistrict(?string $district): string
    {
        if (empty($district)) {
            return 'Central Regional Depot';
        }
        $capital = self::getDistrictCapital($district);
        return "{$district} District Depot ({$capital})";
    }

    /**
     * Get full nested structure: Province -> metadata -> districts with designated capital depots.
     *
     * @return array<string, array{code: string, capital: string, districts: array<int, array{district: string, capital: string, depot: string}>}>
     */
    public static function getProvincesWithDistrictsAndCapitals(): array
    {
        $result = [];
        foreach (self::PROVINCES as $pName => $pData) {
            $districtsList = [];
            foreach ($pData['districts'] as $d) {
                $capital = self::getDistrictCapital($d);
                $districtsList[] = [
                    'district' => $d,
                    'capital' => $capital,
                    'depot' => "{$d} District Depot ({$capital})",
                ];
            }
            $result[$pName] = [
                'code' => $pData['code'],
                'alias' => $pData['alias'] ?? '',
                'capital' => $pData['capital'],
                'districts' => $districtsList,
            ];
        }
        return $result;
    }

    /**
     * Get JSON representation of provinces with districts and their capitals.
     */
    public static function getProvincesWithCapitalsJson(): string
    {
        return json_encode(self::getProvincesWithDistrictsAndCapitals(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

    /**
     * Get flat list of all 77 districts with their capital depot metadata.
     */
    public static function getAllDistrictsWithCapitals(): array
    {
        $all = [];
        foreach (self::getAllDistricts() as $d) {
            $all[] = [
                'district' => $d,
                'province' => self::getProvinceForDistrict($d),
                'capital' => self::getDistrictCapital($d),
                'depot' => self::getDepotNameForDistrict($d),
            ];
        }
        return $all;
    }
}
