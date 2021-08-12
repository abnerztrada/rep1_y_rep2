<?php
// namespace tool_poblacion\libraries;

class Xportxls{
        private $data = array();
        private $stringTable;
        public function __construct ($info,$export){
            if($export== true){
                header("Content-type: application/vnd.ms-excel.xls");
                header("Content-Disposition: attachment;filename=reporte de curso.xls");
            }
            $this->data = $info;
        }
        public function genString($border){
            $size = count($this->data[0]);
            if($border == true){
                $this->stringTable = "<table border=1>";
            }
            else{
                $this->stringTable = "<table border=0>";
            }
            $count=0;
            foreach($this->data as $info){
                $this->stringTable .= "<tr>";
                while ($temp = current($info)) {
                    if($count == 0){
                        $this->stringTable .='<td style = "background-color: silver">';
                        $this->stringTable .= utf8_decode($info[key($info)]);
                        next($info);
                        $this->stringTable .='</td>';
                    }else{
                        $this->stringTable .='<td>';
                        $this->stringTable .= utf8_decode($info[key($info)]);
                        next($info);
                        $this->stringTable .='</td>';
                    }
                }
                $count++;
                $this->stringTable .= "</tr>";
            }
            $this->stringTable .= "</table>";
            echo $this->stringTable;
        }

    }
?>
