<?php 

namespace Bnh\TranslatableFieldBundle\Helpers;

use Symfony\Bridge\Doctrine\RegistryInterface as RegistryInterface;
use Symfony\Component\Form\Form as Form;
use Symfony\Component\PropertyAccess\PropertyAccess as PropertyAccess;
use Doctrine\ORM\Query as Query;
use Gedmo\Translatable\TranslatableListener as TranslatableListener;

class TranslatableFieldManager
{

    CONST GEDMO_TRANSLATION = 'Gedmo\\Translatable\\Entity\\Translation';
    CONST GEDMO_TRANSLATION_WALKER = 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker';

    protected $em;

    public function __construct(RegistryInterface $reg)
    {
        $this->em = $reg->getManager();
    }

    // SELECT
    public function getTranslatedFields($entity, $fieldName, $defaultLocale)
    {
        // 1/3 fallback value
        $class = \get_class($entity);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $identifierField = $this->em->getClassMetadata($class)->getIdentifier()[0]; // <- none composite keys only
        $identifierValue = $propertyAccessor->getValue($entity, $identifierField);
        $entityInDefaultLocale = $this->em->getRepository($class)->createQueryBuilder('entity')
            ->select("entity")
            ->where("entity.$identifierField = :identifier")
            ->setParameter('identifier', $identifierValue)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(false)
            ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, self::GEDMO_TRANSLATION_WALKER)
            ->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $defaultLocale)
            ->getSingleResult();

        // 2/3 translations
        $translations = $this->em->getRepository(self::GEDMO_TRANSLATION)->findTranslations($entity);
        $translations = \array_map(function($element) {
            return \array_shift($element);
        }, $translations);

        // 3/3 translations + default
        $translations[$defaultLocale] = $propertyAccessor->getValue($entityInDefaultLocale, $fieldName);

        return $translations;
    }

    // UPDATE
    public function persistTranslations(Form $form, $locales, $defaultLocale)
    {
        $entity = $form->getParent()->getData();
        $fieldName = $form->getName();
        $submittedValues = $form->getData();
        $gedmoTranlationRespository = $this->em->getRepository(self::GEDMO_TRANSLATION);

        foreach ($locales as $locale) {
            if (array_key_exists($locale, $submittedValues) && (($value = $submittedValues[$locale]) !== NULL)) {
                $gedmoTranlationRespository->translate($entity, $fieldName, $locale, $value);
            }
        }

        $this->em->persist($entity);
        $this->em->flush();
    }
}
