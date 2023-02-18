<?php
    /**
     * Uguu
     *
     * @copyright Copyright (c) 2022-2023 Go Johansson (nokonoko) <neku@pomf.se>
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <https://www.gnu.org/licenses/>.
     */
    
    namespace Pomf\Uguu\Classes;
    
    class CuteGrills
    {
        public array $GRILLS;
        
        /**
         * Loads the list of grills, then redirects to a random grill
         */
        public function showGrills():void
        {
            $this->loadGrills();
            if (!headers_sent()) {
                header(
                   'Location: /img/grills/' .
                   $this->GRILLS[array_rand($this->GRILLS)],
                   true,
                   303,
                );
            }
        }
        
        /**
         * Loads the images from the `img/grills/` directory into the `GRILLS` array
         */
        public function loadGrills():void
        {
            $this->GRILLS = array_slice(scandir('img/grills/'), 2);
        }
    }
