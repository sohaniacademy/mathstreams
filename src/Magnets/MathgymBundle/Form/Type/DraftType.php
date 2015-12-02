<?php

/*
 *  This file is part of Mathstreams.
 *
 *  Copyright (c) 2015  Sohani Academy <developer@sohaniacademy.com>
 *
 *  Mathstreams is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Magnets\MathgymBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
 
class DraftType extends AbstractType
{
  public function buildForm( FormBuilderInterface $builder, array $options )
  {
    $builder->add('saveId','number', array('mapped' => false))
            ->add('label', 'text')
            ->add('question', 'text')
            ->add('hint1', 'text')
            ->add('hint2', 'text')
            ->add('solution', 'text')            
            ->add('save', 'submit', array('label' => 'Submit'));
  }
 
  function getName() {
    return 'DraftType';
  }
}