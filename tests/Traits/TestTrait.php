<?php

namespace App\Tests\Traits;

trait TestTrait
{
    public function assertHasErrors(mixed $entity, int $number = 0): void
    {

        //On initialise le noyau symfony
        self::bootKernel();

        //On test l'entité avec validator
        $errors = self::getContainer()->get('validator')->validate($entity);

        //On instancie un tableau vide pour stocker les erreurs
        $messageErrors = [];

        // On boucle sur les erreurs
        foreach ($errors as $error) {
            // On stocke les erreurs dans le tableau
            $messageErrors[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }

        $this->assertCount($number, $errors, implode(', ', $messageErrors));
    }
}
