<?php
namespace Bnh\TranslatableFieldBundle\Helpers;

use Symfony\Bridge\Doctrine\RegistryInterface as RegistryInterface;
use Symfony\Component\Form\Form as Form;

class TranslatableFieldManager
{
    protected $em;
    protected $translationsRepository;
        
    public function __construct(RegistryInterface $reg)
    {
        $this->em = $reg->getManager();
        $this->translationsRepository = $this->em->getRepository('Gedmo\Translatable\Entity\Translation');
    }
    
    // fetch fields
    public function getTranslatedFields($class, $field, $id)
    {
        // get entitymanager, get entity
        $em = $this->em;
        $entity = $em->getRepository($class)->find($id);
        
        // search translations for entity
        $translations = $this->translationsRepository->findTranslations($entity);
        return $translations;
    }
    
    // persist
    public function persistTranslations(Form $form, $class, $field, $id, $locales)
    {
        // data submitted on the form
        $submittedTranslations = $form->getData();

        // get entity, get stored translations
        $em = $this->em;
        $repository = $em->getRepository($class);
        $entity = $repository->find($id);
        $storedTranslations = $this->translationsRepository->findTranslations($entity);
        
        foreach($submittedTranslations as $formFieldName => $formContent)
        {
            // if the formfield is a translation
            if(in_array($formFieldName, $locales))
            {
                $this->translationsRepository->translate($entity, $field, $formFieldName, $formContent);
            }
        }
        
        $em->persist($entity);
        $em->flush();
    }
}