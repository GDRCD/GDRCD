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

        // Genero categorie
        foreach ( $menu_category as $section ) {

            // Creo array base da aggiungere
            $data = [
                "name" => Filters::out($section['section']),
            ];

            // Estraggo i link
            $links = DB::queryStmt("SELECT * FROM menu WHERE section=:section ORDER BY name", ['section' => Filters::out($section['section'])]);

            // Creo i link
            foreach ( $links as $link ) {
                $name = Filters::out($link['name']);
                $page = Filters::out($link['page']);
                $permission = Filters::out($link['permission']);

                // Se ho i permessi per vedere il link, lo aggiungo
                if ( empty($permission) || Permissions::permission($permission) ) {
                    $data['links'][] = [
                        "page" => $page,
                        "name" => $name,
                    ];
                }
            }

            // Aggiungo l'array formato a quello generale
            $categories[] = $data;
        }

        // Renderizzo il menu'
        return Template::getInstance()->startTemplate()->render('menu', ['categories' => $categories]);
    }
}
