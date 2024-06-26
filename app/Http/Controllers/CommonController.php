<?php

namespace App\Http\Controllers;

use App\Models\API\PageConfig;
use App\Models\Language;
use App\Models\API\Dictionary;
use App\Models\Tenant;
use App\Models\ThemeColor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use File;
use Log;

class CommonController extends Controller
{
    public function globalSettings1(Request $request): JsonResponse
    {
        $data = '{
                "menu":
                [
                    {
                        "url": "/home",
                        "name": "Home",
                        "type": "public",
                        "sub_menu":
                        [
                            {
                                "url": "/news",
                                "name": "News",
                                "type": "public"
                            },
                            {
                                "url": "/books_articles",
                                "name": "Books & Articles",
                                "type": "public"
                            }
                        ]
                    },
                    {
                        "url": "/news",
                        "name": "News",
                        "type": "public",
                        "sub_menu":
                        [
                            {
                                "url": "/books_articles",
                                "name": "Books & Articles",
                                "type": "public",
                                "sub_menu":
                                []
                            },
                            {
                                "url": "/news",
                                "name": "News",
                                "type": "public",
                                "sub_menu":
                                []
                            }
                        ]
                    },
                    {
                        "url": "/books_articles",
                        "name": "Books & Articles",
                        "type": "public",
                        "sub_menu":
                        []
                    },
                    {
                        "url": "/media",
                        "name": "Media",
                        "type": "public",
                        "sub_menu":
                        []
                    },
                    {
                        "url": "/bible",
                        "name": "Bible",
                        "type": "public",
                        "sub_menu":
                        []
                    },
                    {
                        "url": "/holiday",
                        "name": "Holidays",
                        "type": "public",
                        "sub_menu":
                        []
                    },
                    {
                        "url": "/secure/my_donations",
                        "name": "My Donations",
                        "type": "secure",
                        "sub_menu":
                        []
                    },
                    {
                        "url": "/secure/user_profile",
                        "name": "User Profile",
                        "type": "secure",
                        "sub_menu":
                        []
                    },
                    {
                        "url": "/secure/content_manager",
                        "name": "Content Manager",
                        "type": "secure",
                        "sub_menu":
                        []
                    },
                    {
                        "url": "/secure/admin_settings",
                        "name": "Admin Settings",
                        "type": "secure",
                        "sub_menu":
                        []
                    }
                ],
                "langs":
                [
                    {
                        "id": "english",
                        "name": "English"
                    },
                    {
                        "id": "tigrigna",
                        "name": "ትግርኛ"
                    },
                    {
                        "id": "arabic",
                        "name": "عربي"
                    }
                ],
                "theme_colors":
                [
                    {
                        "label": "black",
                        "hexCode": "#000000"
                    },
                    {
                        "label": "Gray",
                        "hexCode": "#808080"
                    },
                    {
                        "label": "purple",
                        "hexCode": "#800080"
                    }
                ],
                "avatar": "DC",
                "labels":
                {
                    "tenant": "Tenant",
                    "document": "Documents",
                    "language": "Language",
                    "settings": "Settings",
                    "languages": "Languages",
                    "theme_mode": "Theme Mode",
                    "page_config": "Page Config",
                    "theme_color": "Theme Color",
                    "search_title": "Search",
                    "translations": "Translations",
                    "admin_settings": "Admin Settings",
                    "other_settings": "Other Settings",
                    "content_manager": "Contents",
                    "action_menu_save": "Save"
                },
                "tenants":
                [
                    {
                        "id": 1801,
                        "name": "Enda Slasie"
                    },
                    {
                        "id": 1802,
                        "name": "Enda Gabr"
                    }
                ],
                "page_types":
                [
                    {
                        "key": "private",
                        "value": "Private"
                    },
                    {
                        "key": "public",
                        "value": "Public"
                    }
                ],
                "content_pages":
                [
                    {
                        "id": 1,
                        "name": "Home",
                        "url":"/home"
                    },
                    {
                        "id": 3,
                        "name": "Books",
                        "url":"/books"
                    },
                    {
                        "id": 4,
                        "name": "Bible",
                        "url":"/books/bible"
                    },
                    {
                        "id": 5,
                        "name": "Images",
                        "url":"/images"
                    }
                ],
                "user_name": "Dave Chapel",
                "authenticated": true,
                "product_relase_no": "1.0.2",
                "default_theme_color": "black"
            }';
        return response()->json(json_decode($data));
    }

  public function globalSettings(Request $request): JsonResponse

{
    $tenantId = $request->header('tenant_id', 0);
    $language = $request->header('language', 'english'); // default to 'english'

    $globalSettings = [];
    Log::info("tenantId ". $tenantId); 

    $userId = null;
    $globalSettings['avatar'] = "GU";
    $globalSettings['user_name'] = "Guest";
    $globalSettings['authenticated'] = false;
    if (auth('sanctum')->user()) {
        $username = auth('sanctum')->user()->name;
        $parts = explode(" ", $username);
        $avatar = substr($parts[0], 0, 1) . substr($parts[1], 0, 1);
        $globalSettings['avatar'] = $avatar;
        $globalSettings['user_name'] = $username;
        $globalSettings['authenticated'] = true;
        $userId = auth('sanctum')->user()->id;
    }

  
    $pageConfig = $this->getPageConfig($tenantId, $userId);
    $globalSettings['menu'] = $this->getMenus($pageConfig);


    $langConfig = Language::select('lang_id', 'lang_name as name')->get()->map(function($lang) {
        return [
            'id' => $lang->lang_id,
            'name' => $lang->name,
        ];
    })->toArray();
    $globalSettings['langs'] = $langConfig;


    $themeColors = ThemeColor::select('label', 'hexCode')->get()->toArray();
    $globalSettings['theme_colors'] = $themeColors;

    // Fetch labels with fallback to English
    $labels = DB::select("
        SELECT 
            en.key, 
            IFNULL(x.value, en.value) AS value 
        FROM 
            (SELECT `key`, `value` FROM `dictionary` WHERE `language` = 'english' AND `tenant_id` = ?) en 
        LEFT JOIN 
            (SELECT `key`, `value` FROM `dictionary` WHERE `language` = ? AND `tenant_id` = ?) x 
        ON 
            en.key = x.key
    ", [$tenantId, $language, $tenantId]);
    $labels = collect($labels)->pluck('value', 'key')->toArray();
    $globalSettings['labels'] = $labels;


    $tenants = Tenant::select('id', 'tenant_name as name')->get()->toArray();
    $globalSettings['tenants'] = $tenants;


    $pageTypes = collect($pageConfig)
        ->pluck('type')
        ->unique()
        ->map(function ($type) {
            return ['key' => $type, 'value' => ucfirst($type)];
        })
        ->values()
        ->toArray();
    $globalSettings['page_types'] = $pageTypes;

    $globalSettings['content_pages'] = $pageConfig;

    $globalSettings['product_release_date'] = File::get(base_path() . "/published.txt");
    $globalSettings['default_theme_color'] = "black";

    $json = json_encode($globalSettings);
    return response()->json(json_decode($json));
}



private function getPageConfig($tenantId, $userId)
{
    $bindings = [$userId, $tenantId];

    $query = "
        SELECT pc.id, pc.page_type AS type, pc.name, pc.page_url AS url, pc.parent, pc.seq_no
        FROM page_config pc
        LEFT JOIN permissions p 
            ON p.page_config_id = pc.id
            AND p.user_id = ?
        WHERE pc.tenant_id = ?
        AND (
            pc.page_type = 'public' 
            OR p.access_level IN ('R', 'RW')
        )
        ORDER BY FIELD(pc.page_type, 'public', 'secure'), pc.seq_no, pc.id
    ";

    if (is_null($userId)) {
        $query = "
            SELECT pc.id, pc.page_type AS type, pc.name, pc.page_url AS url, pc.parent, pc.seq_no
            FROM page_config pc
            WHERE pc.tenant_id = ?
            AND pc.page_type = 'public'
            ORDER BY pc.seq_no, pc.id
        ";
        $bindings = [$tenantId];
    }

    $results = DB::select($query, $bindings);
    return json_decode(json_encode($results), true);
}



private function getMenus($pageConfig)
{
    $itemsByReference = [];

    foreach ($pageConfig as &$item) {
        $itemsByReference[$item['id']] = &$item;
        $itemsByReference[$item['id']]['sub_menu'] = [];
    }

    foreach ($pageConfig as &$item) {
        if ($item['parent'] && $item['parent'] != 0 && isset($itemsByReference[$item['parent']])) {
            $itemsByReference[$item['parent']]['sub_menu'][] = &$item;
        }
    }

    foreach ($itemsByReference as &$item) {
        if (!empty($item['sub_menu'])) {
            usort($item['sub_menu'], function ($a, $b) {
                return $a['seq_no'] <=> $b['seq_no'] ?: $a['id'] <=> $b['id'];
            });
        }
    }

    foreach ($pageConfig as $key => &$item) {
        if ($item['parent'] && isset($itemsByReference[$item['parent']])) {
            unset($pageConfig[$key]);
        }
    }

    return array_values($pageConfig);
}




 
}
