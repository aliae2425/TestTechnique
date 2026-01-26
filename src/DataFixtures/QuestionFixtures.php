<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Question;
use DeepCopy\f001\A;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuestionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $Question = new Question();
        $Question->setTitled('What is the capital of France?');
        $Question->setLevel('Facile');
        $Question->setType('Documentation');
        $Question->setDescription('Choose the correct answer from the options below.');
        $manager->persist($Question);
        for ($i = 0; $i < 4; $i++) {
            $answer = new Answer();
            $answer->setQuestion($Question);
            switch ($i) {
                case 0:
                    $answer->setText('Berlin');
                    $answer->setIsCorrect(false);
                    $answer->setFeedback('Berlin is the capital of Germany.');
                    break;
                case 1:
                    $answer->setText('Madrid');
                    $answer->setIsCorrect(false);
                    $answer->setFeedback('Madrid is the capital of Spain.');
                    break;
                case 2:
                    $answer->setText('Paris');
                    $answer->setIsCorrect(true);
                    $answer->setFeedback('Correct! Paris is the capital of France.');
                    break;
                case 3:
                    $answer->setText('Rome');
                    $answer->setIsCorrect(false);
                    $answer->setFeedback('Rome is the capital of Italy.');
                    break;
            }
            $answer->setQuestion($Question);
            $manager->persist($answer);
        }

        $manager->flush();
    }
}
