<?php

class Menu extends BaseClass
{

    /**
     * @fn createMenu
     * @note Crea un menu da database
     * @param string $menu
     * @return string
     * @throws Throwable
     */
    public function createMenu(string $menu): string
    {
        $menu_category = DB::queryStmt("SELECT section FROM menu WHERE menu_name=:menu GROUP BY section ORDER BY section", ['menu' => Filters::in($menu)]);

        $categories = [];

        foreach ( $menu_category as $section ) {
            $links = DB::queryStmt("SELECT * FROM menu WHERE section=:section ORDER BY name", ['section' => Filters::out($section['section'])]);

            $data = [
                "name" => Filters::out($section['section']),
            ];

            foreach ( $links as $link ) {
                $name = Filters::out($link['name']);
                $page = Filters::out($link['page']);
                $permission = Filters::out($link['permission']);

                if ( empty($permission) || Permissions::permission($permission) ) {
                    $data['links'][] = [
                        "page" => $page,
                        "name" => $name,
                    ];
                }
            }

            $categories[] = $data;

        }

        return Template::getInstance()->startTemplate()->render('menu', ['categories' => $categories]);
    }
}
