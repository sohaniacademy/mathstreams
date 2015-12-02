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

namespace Magnets\MathgymBundle\Service;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use \Magnets\MathgymBundle\Entity\ImageFile;

class GalleryHelperService {

    protected $dbh;

    public function __construct(DBHelperService $dbh) {
        $this->dbh = $dbh;
    }

    public function getAbsolutePath(ImageFile $f) {
        $path = $f->getPath();
        return null === $path ? null : $this->getUploadRootDir() . '/' . $path;
    }

    public function getWebPath(ImageFile $f) {
        $path = $f->getPath();
        return null === $path ? null : $this->getUploadDir() . '/' . $path;
    }

    protected function getUploadRootDir() {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir() {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/documents';
    }

}
