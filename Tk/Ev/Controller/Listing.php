<?php
namespace Tk\Ev\Controller;

use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Listing extends \Bs\Controller\Iface
{
    /**
     * @var string
     */
    protected $templatePath = '';

    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {

    }


    /**
     * @return \Dom\Template
     * @throws \Tk\Db\Exception
     * @throws \Dom\Exception
     */
    public function show()
    {
        $template = parent::show();

        $now = \Tk\Date::create();
        $now = \Tk\Date::floor($now);

        $list = \Tk\Ev\Db\EventMap::create()->findFiltered(array(
            'dateStart' => $now
        ), \Tk\Db\Tool::create('a.dateStart', 35));

        foreach ($list as $i => $event) {
            $row = $template->getRepeat('card');


            $url = \Tk\Uri::create('https://www.google.com/maps/embed/v1/place')
                ->set('key', $this->getConfig()->getGoogleMapKey())
                //->set('q', $event->address)
                ->set('q', $event->mapLat . ','. $event->mapLng)
                ->set('zoom', $event->mapZoom);
            $row->setAttr('gmap', 'src', $url);


            $title = $event->dateStart->format('l, jS M Y') . ' - ' . $event->city . ', ' . $event->state;
            $row->insertText('button', $title);
            $row->insertText('start', $event->dateStart->format('h:i A'));
            $row->insertText('end', $event->dateEnd->format('h:i A'));

            $row->insertText('address', $event->address);
            $row->insertText('rsvp', $event->rsvp);
            $row->insertHtml('description', $event->description);

            // ......

            if ($i == 0) {
                $row->addCss('collapse', 'show');
            }
            $row->setAttr('card-header', 'id', 'heading_'.$i);
            $row->setAttr('collapse', 'aria-labelledby', 'heading_'.$i);

            $row->setAttr('button', 'data-target', '#collapse_'.$i);
            $row->setAttr('button', 'data-controls', 'collapse_'.$i);
            $row->setAttr('collapse', 'id', 'collapse_'.$i);

            $row->appendRepeat();
        }

        $js = <<<JS
jQuery(function($) {
  
  $('#accordionExample').on('shown.bs.collapse', function (e) {
    $('html, body').stop().animate({
        scrollTop: $(e.target).offset().top - 250
    }, 500);
  });
  
});
JS;
        $template->appendJs($js);

        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>

<div class="container">
    <div claas="event-list">

      <div class="accordion" id="accordionExample">


        <div class="card" repeat="card" var="card">
          <div class="card-header" id="headingOne" var="card-header">
            <h5 class="mb-0">
              <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" var="button">
                Collapsible Group Item #1
              </button>
            </h5>
          </div>
          <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample" var="collapse">
            <div class="card-body">
              <div class="row">
                <div class="col-sm-6">
                  <iframe width="100%" height="450" frameborder="0" style="border:0" allowfullscreen="allowfullscreen" var="gmap"></iframe>

                </div>
                <div class="col-sm-6">
                  <p><strong>Time: </strong> <span var="start">6:00pm</span> - <span var="end">8:00pm</span></p>
                  <p><strong>Venue: </strong> <span var="address">Hinterland Hotel. 53 Station Street, Nerang, Qld 4211.</span></p>
                  <p><strong>RSVP: </strong> <span var="rsvp">If you have any questions regarding this event please email safesodaevents@gmail.com</span></p>
                  <div class="description" var="description"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>


    </div>
  </div>
    
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}