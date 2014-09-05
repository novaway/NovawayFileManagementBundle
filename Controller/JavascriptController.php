<?php

namespace Novaway\Bundle\FileManagementBundle\Controller;

use Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Controller
 */
class JavascriptController extends Controller
{
    /**
     * @Route("/js/filemanager.{_format}", requirements={"id" = "json"}, name="novaway_filemanagement_routing_js", defaults={"_format"="js"})
     */
    public function indexAction(Request $request, $_format)
    {
        $serviceManagerId = $request->get('service_manager');
        if (empty($serviceManagerId)) {
            throw new \RuntimeException('service_manager argument is missing.');
        }

        if (!$this->has($serviceManagerId)) {
            throw new NotFoundHttpException(sprintf('The %s service does not exist.', $serviceManagerId));
        }

        /** @var \Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithImageManager $manager */
        $manager = $this->get($serviceManagerId);
        if (!$manager instanceof BaseEntityWithImageManager) {
            throw new \RuntimeException('The manager must be an instance of BaseEntityWithImageManager.');
        }

        $content = json_encode([
            'webpath' => $manager->getWebPath(),
            'image_formats' => $manager->getImageFormatChoices(),
        ]);

        if (null !== $jsManager = $request->query->get('manager')) {
            $content = "var $jsManager = new novaway.FileManager(); $jsManager.setData($content);";
        }

        return new Response($content, 200, array('Content-Type' => $request->getMimeType($_format)));
    }
} 
