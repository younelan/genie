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
        $twig->addFunction(new \Twig\TwigFunction('get_translation', function ($str) {
            return get_translation($str); 
        }));
        $twig->addFunction(new \Twig\TwigFunction('getGenderSymbol', function ($str) {
            return getGenderSymbol($str); 
        }));
        

        $renderedContent = $twig->render($template_name, $variables);
    
        return $renderedContent;
    }
    public function render_master($data) {
        $master_file = $this->basedir . "/templates/master.tpl";
        $data["app_title"] = $this->config['app_name']??"Genie";
        $data["section"] = $data["section"]??"";

        $content_file = $this->basedir . "/templates/" . $data["template"];
        $data['content'] = $this->render_file($content_file, $data);

        return $this->render_file($master_file, $data);

    }


}
class AppModel extends AppBase {

}
class AppController extends AppBase {
    
}