<?php

class Error404 extends Exception {

    public function view() {
        $template = Di::get('Template');
        if(!$template)
             $template = new core\Template();
        $template->render(new core\Response(array(), 'error404'));
    }

}
