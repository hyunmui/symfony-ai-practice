<?php

namespace App\Form;

use App\Entity\Newsletter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewsletterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => '뉴스레터 제목',
                'constraints' => [
                    new NotBlank(message: '제목을 입력해주세요.'),
                    new Length(max: 255, maxMessage: '제목은 255자 이내로 입력해주세요.'),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => '뉴스레터 제목 입력'],
            ])
            ->add('subject', TextType::class, [
                'label' => '이메일 제목',
                'constraints' => [
                    new NotBlank(message: '이메일 제목을 입력해주세요.'),
                    new Length(max: 255, maxMessage: '이메일 제목은 255자 이내로 입력해주세요.'),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => '수신자에게 보여질 이메일 제목'],
            ])
            ->add('content', TextareaType::class, [
                'label' => '뉴스레터 내용',
                'constraints' => [
                    new NotBlank(message: '내용을 입력해주세요.'),
                ],
                'attr' => ['class' => 'form-control', 'rows' => 15, 'placeholder' => '뉴스레터 내용을 입력하거나 AI로 생성하세요'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Newsletter::class,
        ]);
    }
}
