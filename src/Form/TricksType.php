<?php

namespace App\Form;

use App\Entity\Tricks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class TricksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $builder
        ->add('name')
        ->add('description')
        ->add('pictures', FileType::class, [
            'label' => 'Pictures',
            'required' => false,
            'data_class' => null,
            'multiple' => true,
        ])
        ->add('selected_pictures', HiddenType::class, [
        'mapped' => false, // Ne pas mapper ce champ à l'entité Tricks
        ])
        ->add('videos', TextType::class, [
            'label' => 'Videos',
            'required' => false,
        ])
        ->add('category');

       $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $trick = $event->getData();
                $form = $event->getForm();

                if ($trick instanceof Tricks) {
                    $existingVideos = $options['existing_videos'];
                    $videos = implode(',', $existingVideos);
                    $form->add('videos', TextType::class, [
                        'label' => 'Videos',
                        'required' => false,
                        'data' => $videos,
                    ]);
                }
        }); 
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tricks::class,
            'existing_pictures' => [],
            'existing_videos' => [],
        ]);
    }
}
