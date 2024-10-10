<?php 
class AppBase
{

    function render_file($fname, $blocks)
    {

        $contents = @file_get_contents($fname);
        return $this->renderTwigTemplate($contents,$blocks);
        //return $this->render_string($contents,$blocks);
    }
    function render_string($contents, $blocks)
    {
        $helper = new \Opensitez\Simplicity\SimpleTemplate();
        return $helper->render($contents, $blocks);
    }
    function renderTwigTemplate(string $templateContent, array $variables,$template_name="content") {
        if(!is_array($templateContent)) {
            $templateContent = [ "content"=>$templateContent];
        }

        $loader = new \Twig\Loader\ArrayLoader($templateContent);
        $twig = new \Twig\Environment($loader);

        $renderedContent = $twig->render($template_name, $variables);
    
        return $renderedContent;
    }

}
class AppModel extends AppBase {

}
class AppController extends AppBase {
    
}