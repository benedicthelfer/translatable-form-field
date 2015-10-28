<?php
namespace Bnh\TranslatableFieldBundle\Helpers;

use Symfony\Bridge\Doctrine\RegistryInterface as RegistryInterface;
use Symfony\Component\Form\Form as Form;

class TranslatableFieldManager
{
    protected $em;
        
    public function __construct(RegistryInterface $reg)
    {
        $this->em = $reg->getManager();
    }
    
    // call field getter on object
    private function getField($entity, $field)
    {
        $getterFunctionName = 'get'.$field;
        return $entity->{$getterFunctionName}();
    }
    
    // call field setter on object
    private function setField($entity, $field, $value)
    {
        $setterFunctionName = 'set'.$field;
        $entity->{$setterFunctionName}($value);
    }
    
    // construct array from stored fields -> translated[locale][fieldname]
    // fetch fields by *stringify field getter on object
    public function getTranslatedFields($class, $field, $id, $locales, $userLocale)
    {
        // get entitymanager, get entity
        $em = $this->em;
        $entity = $em->getRepository($class)->find($id);
        
        // get data for different locales
        $translated;
        foreach($locales as $localeCode)
        {
            $entity->setTranslatableLocale($localeCode);
            $em->refresh($entity);
            $translated[$localeCode][$field] = $this->getField($entity, $field);
        }
        
        // switch entity locale back to user's locale
        $this->setEntityToUserLocale($entity, $userLocale);
        
        return $translated;
    }
    
    // persist
    public function persistTranslations(Form $form, $class, $field, $id, $locales, $userLocale)
    {
        $translations = $form->getData();

        $em = $this->em;
        $repository = $em->getRepository($class);
        $entity = $repository->find($id);
        
        // loop on locales
        // parse form data
        // get data stored in db
        // set form data on object if needed
        foreach($locales as $locale)
        {
            if(array_key_exists($locale,$translations) && ($translations[$locale] !== NULL))
            {
                $entity->setTranslatableLocale($locale);
                $em->refresh($entity);
                
                $postedValue = $translations[$locale];
                $storedValue = $this->getField($entity, $field);
                
                if($storedValue !== $postedValue)
                {
                    $this->setField($entity, $field, $postedValue);
                    $em->flush();
                }
            }
        }
        
        // switch entity locale back to user's locale
        $this->setEntityToUserLocale($entity, $userLocale);
    }
    
    private function setEntityToUserLocale($entity, $locale)
    {
        $entity->setTranslatableLocale($locale);
        $em->refresh($entity);
    }
}