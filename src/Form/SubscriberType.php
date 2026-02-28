<?php

namespace App\Form;

use App\Entity\Subscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class SubscriberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => '이메일',
                'constraints' => [
                    new NotBlank(message: '이메일을 입력해주세요.'),
                    new Email(message: '올바른 이메일 형식을 입력해주세요.'),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => 'example@email.com'],
            ])
            ->add('name', TextType::class, [
                'label' => '이름',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '이름 (선택)'],
            ])
            ->add('active', CheckboxType::class, [
                'label' => '구독 활성화',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subscriber::class,
        ]);
    }
}
