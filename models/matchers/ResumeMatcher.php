<?php

declare(strict_types=1);

namespace app\models\matchers;

use app\models\LanguageLevel;
use app\models\queries\VacancyQuery;
use app\models\Resume;
use app\models\Vacancy;
use app\models\VacancyLanguage;
use yii\db\conditions\AndCondition;
use yii\db\conditions\OrCondition;
use yii\db\Expression;

final class ResumeMatcher
{
    private Resume $model;

    private ModelLinker $linker;

    private string $comparingTable;

    public function __construct(Resume $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
        $this->comparingTable = Vacancy::tableName();
    }

    public function match(): int
    {
        $this->linker->unlinkMatches();
        $matchesQuery = $this->prepareMainQuery();

        $matches = $matchesQuery->all();
        $matchesCount = count($matches);

        $this->linker->linkMatches($matches);
        $this->linker->linkCounterMatches($matches);

        return $matchesCount;
    }

    private function prepareMainQuery(): VacancyQuery
    {
        return Vacancy::find()
            ->excludeUserId($this->model->user_id)
            ->live()
            ->andWhere($this->buildVacancyLanguagesCondition())
            ->andWhere($this->buildLocationRadiusCondition());
    }

    /**
     * совпадения по локации и радиусу поиска,
     * в случае если удаленка выключена в одном из обьектов или в обоих.
     * если в обоих обьектах включена удаленка - то они найдутся
     */
    private function buildLocationRadiusCondition()
    {
        $radiusExpression = '';

        if ($this->model->search_radius && $this->model->location_lat && $this->model->location_lon) {
            $radiusExpression = new Expression(
                "IF(
                    ({$this->comparingTable}.location_lon AND {$this->comparingTable}.location_lat),
                        ST_Distance_Sphere(
                            POINT({$this->model->location_lon}, {$this->model->location_lat} ),
                            POINT({$this->comparingTable}.location_lon,  {$this->comparingTable}.location_lat)
                        ),0) <= 1000 * {$this->model->search_radius}"
            );
        }

        if ($this->model->remote_on == Resume::REMOTE_ON) {
            $remoteCondition = ["{$this->comparingTable}.remote_on" => Vacancy::REMOTE_ON];

            if ($radiusExpression) {
                return new OrCondition([$remoteCondition, $radiusExpression]);
            } else {
                return $remoteCondition;
            }
        } elseif ($radiusExpression) {
            return $radiusExpression;
        }

        return new Expression($radiusExpression);
    }

    /**
     * Требуемый уровень языка в вакансии соответствует такому же или большему уровню в резюме.
     * Если в вакансии несколько языков, то они все должны быть в резюме (условие AND, языки берутся из профиля пользователя)
     */
    private function buildVacancyLanguagesCondition(): Expression
    {
        $userLanguages = $this->model->languages;
        $expression = '';

        if ($userLanguages) {
            $expression = '(SELECT COUNT(*) FROM ' . VacancyLanguage::tableName() . ' `lang` '
                . 'INNER JOIN ' . LanguageLevel::tableName() . ' ON lang.language_level_id = ' . LanguageLevel::tableName() . '.id '
                . 'WHERE ' . Vacancy::tableName() . '.`id` = `lang`.`vacancy_id` AND (';

            foreach ($userLanguages as $key => $userLanguage) {
                $languageLevel = $userLanguage->level;

                if ($key) {
                    $expression .= ' OR ';
                }

                $expression .= 'lang.language_id = ' . $userLanguage->language_id . ' AND ' . LanguageLevel::tableName() . '.value <= ' . $languageLevel->value;
            }

            $expression .= ')) = (SELECT COUNT(*) FROM `vacancy_language` WHERE `vacancy`.`id` = `vacancy_language`.`vacancy_id`)';
        }

        return new Expression($expression);
    }
}
