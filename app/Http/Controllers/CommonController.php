<?php

namespace App\Http\Controllers;

use App\Models\API\PageConfig;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use stdClass;

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
        $globalSettings = [];


        $globalSettings['menu'] = $this->getMenus();

        //Langs puller
        $lang_config = Language::select("lang_id AS lang_id", "lang_name AS name")->get()->toArray();
        $globalSettings['langs'] = $lang_config;

        $themeColors = [];
        $themeColors[] = ["label" => "black", "hexCode" => "#000000"];
        $themeColors[] = ["label" => "Gray", "hexCode" => "#808080"];
        $themeColors[] = ["label" => "purple", "hexCode" => "#800080"];
        $globalSettings['theme_colors'] = $themeColors;

        $tenants = [];
        $tenants[] = ["id" => "1801", "name" => "Enda Slasie"];
        $tenants[] = ["id" => "1802", "name" => "Enda Gabr"];
        $globalSettings['tenants'] = $tenants;

        $pageTypes = [];
        $pageTypes[] = ["key" => "private", "value" => "Private"];
        $pageTypes[] = ["key" => "public", "value" => "Public"];
        $globalSettings['page_types'] = $pageTypes;

        $contentPages = [];
        $contentPages[] = ["id" => "1", "name" => "Home", "url" => "/home"];
        $contentPages[] = ["id" => "3", "name" => "Books", "url" => "/books"];
        $contentPages[] = ["id" => "4", "name" => "Bible", "url" => "/books/bible"];
        $contentPages[] = ["id" => "5", "name" => "Images", "url" => "/images"];
        $globalSettings['content_pages'] = $contentPages;

        $globalSettings['avatar'] = "DC";
        $globalSettings['user_name'] = "Dave Chapel";
        $globalSettings['authenticated'] = true;
        $globalSettings['product_relase_no'] = "1.0.2";
        $globalSettings['default_theme_color'] = "black";


        // Encode:
        $json = json_encode($globalSettings);
        return response()->json(json_decode($json));
    }

    private function getMenus()
    {
        //Menus puller
        $page_config = PageConfig::select("id", "page_type AS type", "name", "page_url AS url", "parent")->get()->toArray();
        $itemsByReference = array();

        // Build array of item references:
        foreach ($page_config as $key => &$item) {
            $itemsByReference[$item['id']] = &$item;
            // Children array:
            $itemsByReference[$item['id']]['sub_menu'] = array();
            // Empty data class (so that json_encode adds "data: {}" ) 
            // $itemsByReference[$item['id']]['data'] = new stdClass();
        }
        // Set items as children of the relevant parent item.
        foreach ($page_config as $key => &$item)
            if ($item['parent'] && $item['parent'] != 0 && isset($itemsByReference[$item['parent']]))
                $itemsByReference[$item['parent']]['sub_menu'][] = &$item;


        // Remove items that were added to parents elsewhere:
        foreach ($page_config as $key => &$item) {
            if ($item['parent'] && isset($itemsByReference[$item['parent']]))
                unset($page_config[$key]);
        }
        return array_values($page_config);
    }
}
