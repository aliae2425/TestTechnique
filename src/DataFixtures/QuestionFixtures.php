<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuestionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $jsonContent = file_get_contents(__DIR__ . '/data.json');
        $data = json_decode($jsonContent, true);

        foreach ($data as $item) {
            $question = new Question();
            $question->setTitled($item['title']);
            $question->setLevel($item['level']);
            $question->setType($item['type']);
            $question->setDescription($item['Description']);

            foreach ($item['options'] as $option) {
                $answer = new Answer();
                $answer->setText($option['label']);
                $answer->setIsCorrect($option['isCorrect']);
                $answer->setFeedback($option['feedback']);
                
                $question->addReponse($answer);
            }

            $manager->persist($question);
        }

        $manager->flush();
    }
}
