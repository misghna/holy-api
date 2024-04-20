<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonController extends Controller
{
  public function globalSettings(Request $request): JsonResponse
  {
    $data = '{
            "menu": [
              {
                "url": "/home",
                "name": "Home",
                "type": "public",
                "sub_menu": [
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
                "sub_menu": [
                  {
                    "url": "/books_articles",
                    "name": "Books & Articles",
                    "type": "public",
                    "sub_menu": []
                  },
                  {
                    "url": "/news",
                    "name": "News",
                    "type": "public",
                    "sub_menu": []
                  }
                ]
              },
              {
                "url": "/books_articles",
                "name": "Books & Articles",
                "type": "public",
                "sub_menu": []
              },
              {
                "url": "/media",
                "name": "Media",
                "type": "public",
                "sub_menu": []
              },
              {
                "url": "/bible",
                "name": "Bible",
                "type": "public",
                "sub_menu": []
              },
              {
                "url": "/holiday",
                "name": "Holidays",
                "type": "public",
                "sub_menu": []
              },
              {
                "url": "/secure/my_donations",
                "name": "My Donations",
                "type": "secure",
                "sub_menu": []
              },
              {
                "url": "/secure/user_profile",
                "name": "User Profile",
                "type": "secure",
                "sub_menu": []
              },
              {
                "url": "/secure/content_manager",
                "name": "Content Manager",
                "type": "secure",
                "sub_menu": []
              },
              {
                "url": "/secure/admin_settings",
                "name": "Admin Settings",
                "type": "secure",
                "sub_menu": []
              }
            ],
            "langs": [
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
          "theme_colors": [
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
            "labels": {
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
            "tenants": [
              {
                "id": 1801,
                "name": "Enda Slasie"
              },
              {
                "id": 1802,
                "name": "Enda Gabr"
              }
            ],
            "user_name": "Dave Chapel",
            "authenticated": true,
            "product_relase_no": "1.0.2",
            "default_theme_color": "black"
          }';
    return response()->json(json_decode($data));
    // return response()->json([
    //     'data' => json_decode($data),
    // ], 200);
  }
}
