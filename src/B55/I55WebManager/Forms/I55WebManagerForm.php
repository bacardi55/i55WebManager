<?php
namespace B55\I55WebManager\Forms;

class configForms {
  protected $form_factory;

  public function __construct($form_factory) {
    $this->form_factory = $form_factory;
  }

  /**
   * Add/Edit config forms.
   *
   * @param Array $data
   *   $data to send to the createBuilder.
   *
   * @return SfForm
   */
  public function getAddForm($data = array()) {
    $form = $this->form_factory->createBuilder('form', $data)
      ->add('config_name', 'text')
      ->add('config_nb_workspace', 'integer')
      ->getForm();

    return $form;
  }

  /**
   * Add/Edit Client form.
   *
   * @param Array $data
   *   $data to send to the createBuilder.
   *
   * @return SfForm
   */
  public function getClientForm($data = array()) {
    $form = $this->form_factory->createBuilder('form', $data)
      ->add('is_new', 'hidden')
      ->add('name', 'text')
      ->add('command', 'text')
      ->add('arguments', 'text', array('required' => false))
      ->getForm();

    return $form;
  }

  /**
   * Add/Edit workspace.
   *
   * @param Array $data
   *   $data to send to the createBuilder.
   *
   * @return SfForm
   */
  public function getWorkspaceForm($data = array(), $layouts) {
    $form = $this->form_factory->createBuilder('form', $data)
      ->add('name', 'text')
      ->add('default_layout', 'choice', array('choices' => $layouts))
      ->add('is_new', 'hidden')
      ->getForm();

    return $form;
  }

  /**
   * Upload your config file form.
   *
   * @param Array $data
   *   $data to send to the createBuilder method.
   *
   * @return SfForm.
   */
  public function getUploadConfigForm($data = array()) {
    $form = $this->form_factory->createBuilder('form', $data)
      ->add('config_file', 'file')
      ->getForm();

    return $form;
  }
}
