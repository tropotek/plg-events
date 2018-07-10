<?php
namespace Tk\Ev\Controller\Admin\Event;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Bs\Controller\AdminIface
{

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \Tk\Ev\Db\Event
     */
    private $event = null;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Event Edit');
    }


    /**
     *
     * @param Request $request
     * @throws Form\Exception
     * @throws \ReflectionException
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        
        $this->event = new \Tk\Ev\Db\Event();
        if ($request->get('eventId')) {
            $this->event = \Tk\Ev\Db\Event::getMapper()->find($request->get('eventId'));
        }

        $this->form = $this->getConfig()->createForm('event-edit');
        $this->form->setRenderer($this->getConfig()->createFormRenderer($this->form));


        $tab = 'Details';
        if ($this->event->getId())
            $this->form->addField(new Field\Input('title'))->setTabGroup($tab);
        $this->form->addField(new Field\DateRange('date'))->setType(Field\DateRange::TYPE_DATETIME)->setRequired()->setTabGroup($tab);
        $this->form->addField(new Field\Input('rsvp'))->setRequired()->setTabGroup($tab);
        $this->form->addField(new Field\Checkbox('active'))->setTabGroup($tab);
        $this->form->addField(new Field\Textarea('notes'))->setTabGroup($tab)->setNotes('Notes only visible to admin users.');

        $tab = 'Description';
        $this->form->addField(new Field\Textarea('description'))->addCss('mce')->setTabGroup($tab)->setNotes('');

        $tab = 'Venue';
        $this->form->addField(new Field\Hidden('street'));
        $this->form->addField(new Field\Hidden('city'));
        $this->form->addField(new Field\Hidden('state'));
        $this->form->addField(new Field\Hidden('postcode'));
        $this->form->addField(new Field\Hidden('country'));
        $this->form->addField(new Field\Input('address'))->setRequired()->setTabGroup($tab)
            ->setNotes('Select a location on the map or enter the address manually');
        $this->form->addField(new Field\GmapSelect('map'))->setTabGroup($tab);


        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Link('cancel', $this->getConfig()->getBackUrl()));

        $this->form->load(\Tk\Ev\Db\EventMap::create()->unmapForm($this->event));
        
        $this->form->execute();

    }

    /**
     * @param \Tk\Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \ReflectionException
     * @throws \Tk\Db\Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with data from the form using a helper object
        \Tk\Ev\Db\EventMap::create()->mapForm($form->getValues(), $this->event);

        // Password validation needs to be here
        $form->addFieldErrors($this->event->validate());

        if ($form->hasErrors()) {
            return;
        }

        $this->event->save();


        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect(\Tk\Uri::create());
        if ($form->getTriggeredEvent()->getName() == 'update') {
            $event->setRedirect($this->getConfig()->getBackUrl());
        }
    }

    /**
     * @return \Dom\Template
     * @throws \Dom\Exception
     */
    public function show()
    {
        $template = parent::show();
        
        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());

        $template->appendJsUrl(\Tk\Uri::create('/plugin/events/Tk/Ev/Controller/Admin/Event/jquery.tkAddress.js'));
        // Add the script to make the address fields work together with the street autocomplete and map clicks
        $js = <<<JS
jQuery(function ($) {
  $('.tk-form').tkAddress();
});
JS;
        $template->appendJs($js);


        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>

  <div class="panel panel-default">
    <div class="panel-heading">
      <i class="fa fa-calendar fa-fw"></i> Events
    </div>
    <div class="panel-body">
        <div var="form"></div>
    </div>
  </div>
    
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}