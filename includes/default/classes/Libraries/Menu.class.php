<?php

class Menu extends BaseClass{


    /**
     * @fn costantList
     * @note Creazione della sezione della gestione delle costanti
     * @return string
     */
    public function createMenu($menu):string{

        $html = '<div class="menu_box">';
        $page = Filters::in($menu);

        $menu_category = DB::query("SELECT section FROM menu WHERE menu_name='{$menu}' GROUP BY section ORDER BY section",'result');

        foreach ($menu_category as $section) {
            $section_name = Filters::out($section['section']);

            $html .= "<div class='single_section'>";
            $html .= "<div class='section_title'>{$section_name}</div>";
            $html .= "<div class='box_input'>";

            $menu_list = DB::query("SELECT * FROM menu WHERE section='{$section_name}' ORDER BY name",'result');

            foreach ($menu_list as $menu_element){
                $name = Filters::out($menu_element['name']);
                $page = Filters::out($menu_element['page']);
                $permission = Filters::out($menu_element['permission']);


                if(empty($permission) || Permissions::permission($permission)) {
                    $html .= "<a href='main.php?page={$page}'>";
                    $html .= "<div class='single_menu'>{$name}</div>";
                    $html .= "</a>";
                }
            }

            $html .= "</div>";
            $html .= "</div>";
        }

        $html .= "</div>";

        return $html;
    }
}
