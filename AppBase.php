<?php 
class AppBase
{

    function render_file($fname, $blocks)
    {

        $contents = @file_get_contents($fname);
        return $this->render_string($contents,$blocks);
    }
    function render_string($contents, $blocks)
    {
        $helper = new \Opensitez\Simplicity\SimpleTemplate();
        return $helper->render($contents, $blocks);
    }

}
class AppModel extends AppBase {

}
class AppController extends AppBase {
    
}