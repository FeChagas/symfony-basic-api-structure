<?php
namespace App\Service;

class Utils
{
    public function validateQuery($query)
    {
        $params = explode('+', $query);

        $opt = array();
        $opt['where'] = null;
        $opt['sort'] = null;

        $options = array();

        foreach ($params as $key => $value) 
        {
            if (strpos($value, 'where=') !== false) 
            {
                $where = explode('where=', $value);
                $opt['where'] = $where[1];

                if(strpos($opt['where'], '_created_at') !== false)
                {
                    $dates = explode(' AND ', substr($opt['where'], strpos($opt['where'], '_created_at')+19, 26));
                    $options['where']['created_at']['start'] = (new \DateTime($dates[0]))->setTime(0, 0, 0);
                    $options['where']['created_at']['end'] = (new \DateTime($dates[1]))->setTime(23, 59, 59);
                }
                if(strpos($opt['where'], '_user') !== false)
                {
                    $ct = substr($opt['where'], strpos($opt['where'], '_user')+6, 99);
                    $ct = substr($ct, 0, strpos($ct, '_'));
                    $options['where']['user'] = $ct;
                }
                if(strpos($opt['where'], '_store') !== false)
                {
                    $ct = substr($opt['where'], strpos($opt['where'], '_store')+7, 99);
                    $ct = substr($ct, 0, strpos($ct, '_'));
                    $options['where']['store'] = $ct;
                }
                if(strpos($opt['where'], '_quiz') !== false)
                {
                    $ct = substr($opt['where'], strpos($opt['where'], '_quiz')+6, 99);
                    $ct = substr($ct, 0, strpos($ct, '_'));
                    $options['where']['quiz'] = $ct;
                }
                if(strpos($opt['where'], '_training') !== false)
                {
                    $ct = substr($opt['where'], strpos($opt['where'], '_training')+10, 99);
                    $ct = substr($ct, 0, strpos($ct, '_'));
                    $options['where']['training'] = $ct;
                }
                if(strpos($opt['where'], '_category') !== false)
                {
                    $ct = substr($opt['where'], strpos($opt['where'], '_category')+10, 99);
                    $ct = substr($ct, 0, strpos($ct, '_'));
                    //A lot is using underscore (_) to slugfy the content, for example the categories of support content materials, in that case, to meet the needs of the query search, those slugs are send with hyphen (-) and replaced here to match with the database content
                    $ct = str_replace('-', '_', $ct); 
                    $options['where']['category'] = $ct;
                }
            }

            if (strpos($value, '_sort=') !== false) 
            {
                $sort = explode('_sort=', $value);
                $options['sort'] = explode(' ', $sort[1]);
            }
        }

        return $options;
    }

    public function triggerRoutine(\App\Entity\Routine $routine)
    {
        $run = false;

        $now = new \DateTime();
        $interval = $now->diff($routine->getLastTimeTriggered());

        if ($routine->getFrequency()->getName() == 'everyThreeMinutes' && ($interval->y >= 0 && $interval->m >= 0 && $interval->d >= 0 && $interval->h >= 0 && $interval->i >= 3 && $interval->s >= 0 && $interval->f >= 0)) 
        {                
            $run = true;
        }
        if ($routine->getFrequency()->getName() == 'everyTenMinutes' && ($interval->y >= 0 && $interval->m >= 0 && $interval->d >= 0 && $interval->h >= 0 && $interval->i >= 10 && $interval->s >= 0 && $interval->f >= 0)) 
        {                
            $run = true;
        }
        elseif ($routine->getFrequency()->getName() == 'everyHour' && ($interval->y >= 0 && $interval->m >= 0 && $interval->d >= 0 && $interval->h >= 1 && $interval->i >= 0 && $interval->s >= 0 && $interval->f >= 0)) 
        {
            $run = true;
        }
        elseif ($routine->getFrequency()->getName() == 'everySixHours' && ($interval->y >= 0 && $interval->m >= 0 && $interval->d >= 0 && $interval->h >= 6 && $interval->i >= 0 && $interval->s >= 0 && $interval->f >= 0)) 
        {
            $run = true;
        }
        elseif ($routine->getFrequency()->getName() == 'Daily' && ($interval->y >= 0 && $interval->m >= 0 && $interval->d >= 1 && $interval->h >= 0 && $interval->i >= 0 && $interval->s >= 0 && $interval->f >= 0)) 
        {
            $run = true;
        }
        elseif ($routine->getFrequency()->getName() == 'Weekly' && ($interval->y >= 0 && $interval->m >= 0 && $interval->d >= 7 && $interval->h >= 0 && $interval->i >= 0 && $interval->s >= 0 && $interval->f >= 0)) 
        {
            $run = true;
        }
        elseif ($routine->getFrequency()->getName() == 'Monthly' && ($interval->y >= 0 && $interval->m >= 1 && $interval->d >= 0 && $interval->h >= 0 && $interval->i >= 0 && $interval->s >= 0 && $interval->f >= 0)) 
        {
            $run = true;
        }

        return $run;
    }
}