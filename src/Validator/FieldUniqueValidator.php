<?php

namespace App\Validator;

use App\Validator\FieldUnique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\ConstraintValidatorInterface;


class FieldUniqueValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {

    }

    /**
     * Функция проверки валидности значения
     *
     * @param mixed $value Проверяемое значение
     * @param Constraint $constraint Ограничение для валидации
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        /** @var CategoryRepository $repository */
        $repository = $this->entityManager->getRepository(Category::class);
        $category = $repository->findOneBy(
            ['name' => $value],
        );

        if ($category !== null) {
            $this->fail($constraint);
        }
    }

    private function fail(Constraint $constraint)
    {
        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }


}