<?php
/*
 * This class works with ods, odt file in CakePHP style data template
 * like: [Document.name]
 * 
 * before using
 * add to controller components array
 * 
 * required shell commands:
 *      zip
 *      unzip
 *      cp
 *      cd
 * 
 * Tested on LibreOffice 4.2, Ubuntu 14.04
 * 
 * BUGS AND WARNINGS
 * ODS:
 *  pictures:
 *  area ranges
 * 
 */
App::uses('Component', 'Controller');

class OpenDocumentTemplateComponent extends Component {
    var
            $filename,
            $content_dir,
            $template_file,
            $string_options = array('before' => '[', 'after' => ']' ),
            $tmpDirName = 'OpenDocumentTemplate',
            $userProfile = 'user',
            $result_file;

    public function unzip($template_file = null) {

        if (!$template_file)
            $template_file = $this->filename;

        if (!file_exists($template_file))
            return false;

        $md5 = md5_file($template_file);
        $this->md5 = $md5;
        
        if ( Configure::read('login_username') ) 
            $this->userProfile =  Configure::read('login_username');
        
        $tmp_dir = TMP . DS . 'cache' . DS . $this->tmpDirName . DS . $this->userProfile . DS . basename($template_file)  . DS . $md5;
        
        $this->content_xml = $tmp_dir . DS . 'source' .  DS . 'content.xml';
        $this->styles_xml = $tmp_dir .  DS . 'source' . DS . 'styles.xml';
        
        if (!is_dir($tmp_dir)) {
            mkdir($tmp_dir . DS . 'source', 0755, true);
            mkdir($tmp_dir . DS . 'out', 0755, true);
            exec("cd $tmp_dir/source; unzip $template_file; cp * -r ../out ");
        }

        $this->content_dir = $tmp_dir;

        return $tmp_dir;
    }

    public function zip($result_file) {
        if (file_exists($result_file))
                unlink($result_file);
        
        exec(" cd {$this->content_dir}/out; zip $result_file  -r .");
    }

    private function read_meta() {

        if (!$this->unzip())
            return false;
        $meta_filename = $this->content_dir . DS . 'source' . DS . 'meta.xml';
        $meta_xml = file_get_contents($meta_filename);

        $xml = Xml::build($meta_xml);
        $this->meta = Xml::toArray($xml);

        $this->meta_user = array();

        if (!empty($this->meta['document-meta']['office:meta']['meta:user-defined']['@meta:name']) == 1)
            $this->meta['document-meta']['office:meta']['meta:user-defined'] = array(
                $this->meta['document-meta']['office:meta']['meta:user-defined']
            );

        if (empty($this->meta['document-meta']['office:meta']['meta:user-defined'])) {
            return false;
        }

        foreach ($this->meta['document-meta']['office:meta']['meta:user-defined'] as $m) {
            $cp1251 = Configure::read('App.encoding') == 'CP1251';
            $name = $cp1251 ? iconv('utf-8', 'cp1251', $m['@meta:name']) : $m['@meta:name'];

            $type = empty($m['@meta:value-type']) ? 'string' : $m['@meta:value-type'];
            $value = $cp1251 ? iconv('utf-8', 'cp1251', $m['@']) : $m['@'];

            $this->meta_user[$name] = $value;

            /*
             * @meta:name
             */
        }

        $this->options = Hash::expand($this->meta_user);
    }

    public function test1_data() {
        return array(
            'Report' => array(
                'user' => 'www-data',
                'name' => 'Continents and countries',
                'number' => 123,
                'float' => 345.67,
                'currency' => 56.7,
            ),
            'Continents' => array(
                array(
                    'Continent' => array(
                        'name' => 'Asia',
                        'area' => 1234
                    ),
                    'Countries' => array(
                        array(
                            'Country' => array(
                                'name' => 'China',
                                'area' => 123.34
                            ),
                            'Cities' => array(
                                array(
                                    'City' => array(
                                        'name' => 'Pekin',
                                        'river' => 'yes'
                                    )
                                ),
                                array(
                                    'City' => array(
                                        'name' => 'Shanghai',
                                        'river' => 'no'
                                    )
                                ),
                            )
                        ),
                        array(
                            'Country' => array(
                                'name' => 'India',
                                'area' => 13.34
                            ),
                            'Cities' => array(
                                array(
                                    'City' => array(
                                        'name' => 'Deli',
                                        'river' => 'yes'
                                    )
                                ),
                                array(
                                    'City' => array(
                                        'name' => 'Jambur',
                                        'river' => 'no'
                                    )
                                ),
                            )
                        ),
                    )
                ),
                array(
                    'Continent' => array(
                        'name' => 'Europe',
                        'area' => 3452
                    ),
                    'Countries' => array(
                        array(
                            'Country' => array(
                                'name' => 'Russia',
                                'area' => 13.34
                            ),
                            'Cities' => array(
                                array(
                                    'City' => array(
                                        'name' => 'Samara',
                                        'river' => 'Volga'
                                    )
                                ),
                                array(
                                    'City' => array(
                                        'name' => 'Moscow',
                                        'river' => 'yes'
                                    )
                                ),
                                array(
                                    'City' => array(
                                        'name' => 'Penza',
                                        'river' => 'yes'
                                    )
                                ),
                            )
                        )
                    )
                ),
                array(
                    'Continent' => array(
                        'name' => 'Africa',
                        'area' => 2311.23
                    ),
                    'Countries' => array(
                        array(
                            'Country' => array(
                                'name' => 'Nigeria',
                                'area' => 34.43
                            ),
                            'Cities' => array(
                                array(
                                    'City' => array(
                                        'name' => 'Main grad',
                                        'river' => 'Niger'
                                    )
                                ),
                                array(
                                    'City' => array(
                                        'name' => 'Main grad',
                                        'river' => 'Niger'
                                    )
                                ),
                                array(
                                    'City' => array(
                                        'name' => 'Main grad',
                                        'river' => 'Niger'
                                    ),
                                )
                            ),
                        ),
                        array(
                            'Country' => array(
                                'name' => 'UAR',
                                'area' => 56.43
                            ),
                            'Cities' => array(
                                array(
                                    'City' => array(
                                        'name' => 'Yohanesburg',
                                        'river' => 'ocean'
                                    )
                                ),
                                array(
                                    'City' => array(
                                        'name' => '9 district',
                                        'river' => 'no'
                                    )
                                ),
                            ),
                        ),
                        array(
                            'Country' => array(
                                'name' => 'Egypt',
                                'area' => 87.5
                            ),
                            'Cities' => array(
                                array(
                                    'City' => array(
                                        'name' => 'Kair',
                                        'river' => 'Nill'
                                    )
                                ),
                                array(
                                    'City' => array(
                                        'name' => 'Sharm and sheih',
                                        'river' => 'sea'
                                    )
                                ),
                                array(
                                    'City' => array(
                                        'name' => 'Kair',
                                        'river' => 'Nill'
                                    ),
                                ),
                                array(
                                    'City' => array(
                                        'name' => 'Sharm and sheih',
                                        'river' => 'sea'
                                    )))),
                    )
                ))
        );
    }

    public function spreadsheet_render() {
        /*
         * detect ranges
         */
        $expr_ix = 0;

        foreach ($this->ods['named-expression'] as $expression_name => $expr) {

            $sheet_ix = 0;
            /* if (empty($this->options['Range'][ $expr['name'] ]['key']))
              $this->options['Range'][ $expr['name'] ]['key'] = $expr['name'];
              $expr['key'] = $this->options['Range'][ $expr['name'] ]['key']; */

            $expr['modelName'] = $expr['name'];
            if (strpos($expression_name, '__') !== false) {
                list($keyName, $num) = explode('__', $expression_name);
                if (is_numeric($num))
                    $expr['modelName'] = $keyName;
            }

            $this->ods['named-expression'][$expression_name] = $expr;

            foreach ($this->ods['office:spreadsheet']['table:table'] as $sheet) {

                if ($sheet['attr']['table:name'] == $expr['sheet']) {
                    /*
                     * read cell templates
                     */

                    /* $range_rows = array_slice(
                      $this->ods['office:spreadsheet']['table:table'][$sheet_ix]['rows'],
                      $expr['start'] - 1,
                      $expr['length']); */
                }
                $sheet_ix++;
            }

            $expr_ix++;
        }

        /*
         * analyze ranges
         */

        $this->ods['range-blocks'] = array();

        foreach ($this->ods['named-expression'] as $expression_name1 => $expr1) {

            foreach ($this->ods['named-expression'] as $expression_name2 => $expr2) {
                if ($expr1['name'] != $expr2['name']) {
                    $expression_master_detail = $this->expression_master_detail($expr1, $expr2);
                    $expressions_in_block = $this->expressions_in_block($expr1, $expr2);

                    if ($expression_master_detail && $expressions_in_block) {
                        //$this->expr_to_range_block($expr1);
                        //$this->expr_to_range_block($expr2);
                        $this->ods['named-expression'][$expr1['name']]['children'][] = $expr2['name'];
                        $this->ods['named-expression'][$expr2['name']]['parent'][] = $expr1['name'];
                    }
                }
            }
        }

        /*
         * ����� ����� ��������� �������
         */
        foreach ($this->ods['named-expression'] as $expression_name => $expr) {
            if (!empty($this->ods['named-expression'][$expression_name]['parent'])) {
                if (is_array($this->ods['named-expression'][$expression_name]['parent'])) {


                    if (count($this->ods['named-expression'][$expression_name]['parent']) > 1) {
                        /*
                         * ���� ��������� ������� ���������, �������� ������ ����
                         * ��� �����, � ������������� � ���� ������ length
                         */
                        $nearest = array(
                            'name' => false,
                            'length' => 1000000
                        );
                        foreach ($this->ods['named-expression'][$expression_name]['parent'] as $par) {
                            if (
                                    $this->ods['named-expression'][$par]['length'] <
                                    $nearest['length']
                            ) {
                                $nearest = $this->ods['named-expression'][$par];
                            }
                        }
                        if ($nearest['name']) {
                            /*
                             * ������� ����� children �� ����� ������� �� 
                             * ��������� �������
                             */

                            foreach ($this->ods['named-expression'][$expression_name]['parent'] as $par) {
                                if ($this->ods['named-expression'][$par]['name'] != $nearest['name']
                                ) {
                                    unset($this->ods['named-expression'][$par]['children'][$expression_name]);
                                }
                            }
                            $this->ods['named-expression'][$expression_name]['parent'] = $nearest['name'];
                        }
                    }

                    if (count($this->ods['named-expression'][$expression_name]['parent']) == 1) {
                        if (is_array($this->ods['named-expression'][$expression_name]['parent']))
                            $this->ods['named-expression'][$expression_name]['parent'] = $this->ods['named-expression'][$expression_name]['parent'][0];
                    }
                }
            }
        }

        /*
         * ���������� ����� ��������
         */
        foreach ($this->ods['named-expression'] as $expression_name => $expr) {
            if (
                    !empty($this->ods['named-expression'][$expression_name]['children'])
            ) {
                if (is_array($this->ods['named-expression'][$expression_name]['children'])) {
                    $chi_ix = 0;
                    foreach ($this->ods['named-expression'][$expression_name]['children'] as $childdren_name) {
                        if ($this->ods['named-expression'][$childdren_name]['parent'] != $expression_name) {
                            unset($this->ods['named-expression'][$expression_name]['children'][$chi_ix]);
                        }
                        $chi_ix++;
                    }
                }
            }
        }

        /*
         * ������� ����������� ��������
         */
        foreach ($this->ods['named-expression'] as $expression_name => $expr) {
            if (
                    empty($expr['parent']) &&
                    empty($expr['children'])
            ) {
                /*
                 * ��� �� ������� �� ��������
                 */
                $this->ods['named-expression'][$expression_name]['type'] = 'once';
            }
            if (
                    empty($expr['parent']) &&
                    !empty($expr['children'])
            ) {
                /*
                 * ������ ������ � �����
                 */
                $this->ods['named-expression'][$expression_name]['type'] = 'first';
            }
            if (
                    !empty($expr['parent']) &&
                    !empty($expr['children'])
            ) {
                /*
                 * ����� ���������
                 */
                $this->ods['named-expression'][$expression_name]['type'] = 'meddle';
            }
            if (
                    !empty($expr['parent']) &&
                    empty($expr['children'])
            ) {
                /*
                 * ������� ������, ������� ������
                 */
                $this->ods['named-expression'][$expression_name]['type'] = 'last';
            }
        }

        /*
         * ������� �������� ����� �� ��������
         */

        foreach ($this->ods['named-expression'] as $expression_name => $expr) {
            $sheet_number = $this->spreadsheet_get_sheet_index($expr['sheet']);
            switch ($expr['type']) {
                case 'once':
                    /*
                     * ����� �������� ��� ������ ����� ��������
                     */
                    for ($i = $expr['start'] - 1; $i < $expr['end']; $i++) {

                        $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$i]['range'] = $expr['name'];
                    }
                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$expr['start'] - 1]['range_start'] = true;
                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$expr['start'] - 1]['range_length'] = $expr['length'];

                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$expr['end'] - 1]['range_end'] = true;
                    break;

                case 'first':
                    /*
                     * ����� ����� 
                     * ������ �������
                     */
                    for ($i = $expr['start'] - 1; $i < $expr['end']; $i++) {
                        $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$i]['range'] = $expr['name'];
                    }
                    /*
                     * mark 0 level
                     */
                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$expr['start'] - 1]['range_start'] = true;
                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$expr['start'] - 1]['range_length'] = $expr['length'];
                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$expr['end'] - 1]['range_end'] = true;

                    foreach ($expr['children'] as $chi0_name) {
                        $chi0 = $this->ods['named-expression'][$chi0_name];
                        for ($i = $chi0['start'] - 1; $i < $chi0['end']; $i++) {
                            $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$i]['range'] = $chi0['name'];
                        }
                        /*
                         * mark start and end row of range
                         */
                        $this->ods['office:spreadsheet']
                                ['table:table']
                                [$sheet_number]['rows']
                                [$chi0['start'] - 1]['range_start'] = true;
                        $this->ods['office:spreadsheet']
                                ['table:table']
                                [$sheet_number]['rows']
                                [$chi0['start'] - 1]['range_length'] = $chi0['length'];

                        $this->ods['office:spreadsheet']
                                ['table:table']
                                [$sheet_number]['rows']
                                [$chi0['end'] - 1]['range_end'] = true;

                        if (!empty($chi0['children'])) {
                            foreach ($chi0['children'] as $chi1_name) {
                                $chi1 = $this->ods['named-expression'][$chi1_name];

                                for ($i = $chi1['start'] - 1; $i < $chi1['end']; $i++) {
                                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$i]['range'] = $chi1['name'];
                                }
                                /*
                                 * mark start and end row of range
                                 */
                                $this->ods['office:spreadsheet']
                                        ['table:table']
                                        [$sheet_number]['rows']
                                        [$chi1['start'] - 1]['range_start'] = true;
                                $this->ods['office:spreadsheet']
                                        ['table:table']
                                        [$sheet_number]['rows']
                                        [$chi1['start'] - 1]['range_length'] = $chi1['length'];

                                $this->ods['office:spreadsheet']
                                        ['table:table']
                                        [$sheet_number]['rows']
                                        [$chi1['end'] - 1]['range_end'] = true;

                                if (!empty($chi1['children'])) {
                                    foreach ($chi1['children'] as $chi2_name) {
                                        $chi2 = $this->ods['named-expression'][$chi2_name];
                                        for ($i = $chi2['start'] - 1; $i < $chi2['end']; $i++) {
                                            $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'][$i]['range'] = $chi2['name'];
                                        }
                                        /*
                                         * mark start and end row of range
                                         */
                                        $this->ods['office:spreadsheet']
                                                ['table:table']
                                                [$sheet_number]['rows']
                                                [$chi2['start'] - 1]['range_start'] = true;
                                        $this->ods['office:spreadsheet']
                                                ['table:table']
                                                [$sheet_number]['rows']
                                                [$chi2['start'] - 1]['range_length'] = $chi2['length'];

                                        $this->ods['office:spreadsheet']
                                                ['table:table']
                                                [$sheet_number]['rows']
                                                [$chi2['end'] - 1]['range_end'] = true;
                                    }
                                }
                            }
                        }
                    }

                    /*
                     * ����� � �������� �����������
                     */

                    break;

                default:
                    break;
            }
        }
        /*
         * ����� ���������� ������������,
         * ������ ������ ���� ������� � ��������� ������� �����
         */
        $this->range_templates = array();
        $this->range_models = array();
        foreach ($this->ods['named-expression'] as $expression_name => $expr) {
            $sheet_number = $this->spreadsheet_get_sheet_index($expr['sheet']);
            $this->range_models[] = $expr['modelName']; //<! ����� � ���������� ��� ������ ������� �� ���������� � ���� ��������� �

            $this->range_templates[$expression_name] = array_slice(
                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'], $expr['start'] - 1, $expr['length']); /**/

        }

        /*
         * ������ ������ ������ ���� ����� ��� ������
         * ��� ���� �� ����� �� ��������� ������� ������� ����������� ����� �� ����� ����������. ���!
         */
        $data_for_body = $this->data;
        foreach ($this->range_models as $model) {
            if (!empty($this->data[$model])) {
                unset($data_for_body[$model]);
            }
        }
        $this->data_for_body_flatten = Hash::flatten($data_for_body);
        /*
         * ������ ������ ���� ���� ������
         */

        for ($sheet_ix = 0; $sheet_ix < count($this->ods['office:spreadsheet']['table:table']); $sheet_ix++) {
            for ($i = 0; $i < count($this->ods['office:spreadsheet']['table:table'][$sheet_ix]['rows']); $i++) {
                $row = $this->ods['office:spreadsheet']['table:table'][$sheet_ix]['rows'][$i];
                $this->ods['office:spreadsheet']['table:table'][$sheet_ix]['rows'][$i] = $this->spreadsheet_render_row($row, $this->data_for_body_flatten);
            }
        } 

        /*
         * �� � ������ ����� ����������
         * ������ ���� ������ ������ ��� �������� �� ������!
         * ������ � ������ ���� once
         */

        $this->range_render = array(); //������ � �������� �������
        foreach ($this->ods['named-expression'] as $expression_name => $expr) {

            if (!empty($this->range_templates[$expression_name])) {
                if (!empty($expr['modelName'])) {
                    $model = $expr['modelName'];
                    $sheet_number = $this->spreadsheet_get_sheet_index($expr['sheet']);


                    if ($expr['type'] === 'first') {
                        if (!empty($this->data[$model])) {

                            $this->range_render[$expression_name] = array();
                            foreach ($this->data[$model] as $datum0) {

                                /*
                                 * �� ���� ����� ������ �� ��������� ��� �������� ������ 
                                 * � �������������� � ������ �������!!!!!!!
                                 */
                                
                                $datum_flatten0 = array_merge(
                                        Hash::flatten($datum0), $this->data_for_body_flatten // Add a global values
                                );
                                /*
                                 * ��������� � �������
                                 * ���� ������
                                 * ������������ ������ �������  � �����
                                 */
                                $rows0 = array();
                                for ($i = 0; $i < count($this->range_templates[$expression_name]); $i++) {
                                    $row = $this->range_templates[$expression_name][$i];
                                    if ($row['range'] == $expression_name)
                                        $row = $this->spreadsheet_render_row($row, $datum_flatten0);
                                    $rows0[] = $row;
                                }
                                /*
                                 * �������� 3 ������ �����������
                                 */

                                if (!empty($expr['children'])) {
                                    /*
                                     * �������� ������ ��� rows0
                                     */

                                    foreach ($expr['children'] as $expr1_name) {
                                        $rows1 = array(); //��� ���������� ������� ������ �������
                                        //����������� ����� ���� ���������
                                        $expr1 = $this->ods['named-expression'][$expr1_name];
                                        $model1 = $expr1['modelName'];
                                        if (!empty($datum0[$model1])) {
                                            foreach ($datum0[$model1] as $datum1) {
                                                $datum_flatten1 = array_merge(
                                                        Hash::flatten($datum1), $datum_flatten0, $this->data_for_body_flatten
                                                );
                                                /*
                                                 * �� �� ������ ������
                                                 * �������� �=��������� �����
                                                 * �����
                                                 */
                                                for ($i = 0; $i < count($this->range_templates[$expr1_name]); $i++) {
                                                    $row1 = $this->range_templates[$expr1_name][$i];
                                                    if ($row1['range'] == $expr1_name)
                                                        $row1 = $this->spreadsheet_render_row($row1, $datum_flatten1);
                                                    $rows1[] = $row1;
                                                }

                                                if (!empty($expr1['children'])) {
                                                    $rows2 = array();
                                                    foreach ($expr1['children'] as $expr2_name) {
                                                        $expr2 = $this->ods['named-expression'][$expr2_name];
                                                        $model2 = $expr2['modelName'];
                                                        if (!empty($datum1[$model2])) {
                                                            foreach ($datum1[$model2] as $datum2) {
                                                                $datum_flatten2 = array_merge(
                                                                        Hash::flatten($datum2), $datum_flatten1, $datum_flatten0, $this->data_for_body_flatten
                                                                );
                                                                for ($i = 0; $i < count($this->range_templates[$expr2_name]); $i++) {
                                                                    $row2 = $this->range_templates[$expr2_name][$i];
                                                                    if ($row2['range'] == $expr2_name)
                                                                        $row2 = $this->spreadsheet_render_row($row2, $datum_flatten2);
                                                                    $rows2[] = $row2;
                                                                }
                                                            }
                                                        }
                                                        /*
                                                         * ����� ��� �������� ������
                                                         * ���� �������� ������
                                                         */
                                                        list($range_start2, $range_end2, $range_length2) = $this->spreadsheet_get_current_range_pos($rows1, $expr2_name);

                                                        $rows1_before = array_slice($rows1, 0, $range_start2);
                                                        $rows1_after = array_slice($rows1, $range_start2 + $range_length2);
                                                        $rows1 = array_merge(
                                                                $rows1_before, $rows2, $rows1_after
                                                        );
                                                    }
                                                }
                                            }
                                        }

                                        /*
                                         * ��������� ������� ���� ������
                                         */

                                        list($range_start1, $range_end1, $range_length1) = $this->spreadsheet_get_current_range_pos($rows0, $expr1_name);

                                        $rows0_before = array_slice($rows0, 0, $range_start1);
                                        $rows0_after = array_slice($rows0, $range_start1 + $range_length1);
                                        $rows0 = array_merge(
                                                $rows0_before, $rows1, $rows0_after
                                        );
                                    }

                                    /*
                                     * ������������ ���������� ������� � ������ ����
                                     */
                                }

                                /*
                                 * ������ �����������
                                 * � ������ ��������� ������������� ��������
                                 * � ��������� ����������
                                 */

                                $this->range_render[$expression_name] = array_merge(
                                        $this->range_render[$expression_name], $rows0
                                );

                            }
                            /*
                             * ��������� ��������
                             */
                            list($range_start, $range_end, $range_length) = $this->spreadsheet_get_current_range_pos($sheet_number, $expression_name);

                            $rows_before = array_slice(
                                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'], 0, $range_start);
                            $rows_after = array_slice(
                                    $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'], $range_start + $range_length);
                            $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'] = array_merge(
                                    $rows_before, $this->range_render[$expression_name], $rows_after
                            );
                        }
                    }

                    if ($expr['type'] === 'once') {
                        if (!empty($this->data[$model])) {
                            foreach ($this->data[$model] as $datum) {
                                $datum_flatten = array_merge(
                                        Hash::flatten($datum), $this->data_for_body_flatten // Add a global values
                                );
                                /*
                                 * ��������� � �������
                                 */
                                for ($i = 0; $i < count($this->range_templates[$expression_name]); $i++) {
                                    $row = $this->range_templates[$expression_name][$i];
                                    $this->range_render[$expression_name][] = $this->spreadsheet_render_row($row, $datum_flatten);
                                }
                            }
                            /*
                             * ������ ����������� ��������� � ������ ��������� �����
                             */

                            list($range_start, $range_end, $range_length) = $this->spreadsheet_get_current_range_pos($sheet_number, $expression_name);

                            if ($range_start && $range_end && $range_length) {
                                //remove template rows
                                $rows_before = array_slice($this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'], 0, $range_start);
                                $rows_after = array_slice(
                                        $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'], $range_end + 1);
                                $this->ods['office:spreadsheet']['table:table'][$sheet_number]['rows'] = array_merge(
                                        $rows_before, $this->range_render[$expression_name], $rows_after
                                );
                            }
                        }
                    }
                }
            }
        }

    }

    private function spreadsheet_get_current_range_pos($sheet_ix, $range_name) {
        $start = false;
        $end = false;
        $length = false;
        /*
         * reset item keys
         */

        $rows = array();
        if (is_numeric($sheet_ix)) {
            $rows = array_values($this->ods['office:spreadsheet']['table:table'][$sheet_ix]['rows']);
        }

        if (is_array($sheet_ix)) {
            $rows = array_values($sheet_ix);
        }

        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (!empty($row['range'])) {
                if ($row['range'] == $range_name) {
                    if (!empty($row['range_start']))
                        $start = $i;

                    if (!empty($row['range_end']))
                        $end = $i;

                    if (!empty($row['range_length']))
                        $length = $row['range_length'];
                }
            }
        }
        return array(
            $start,
            $end,
            $length
        );
    }

    private function spreadsheet_render_row($row, $flatten_data) {
        if (empty($row['cells']))
            return $row;

        for ($i = 0; $i < count($row['cells']); $i++) {
            if (!empty($row['cells'][$i]['content'])) {
                /*
                 * check string markers "[" and "]"
                 * [Data.field]
                 */
                if (
                        strpos($row['cells'][$i]['content'], $this->string_options['before']) !== false &&
                        strpos($row['cells'][$i]['content'], $this->string_options['after']) !== false
                ) {
                    /*
                     * �� ���� ����� ��� ����������� ����������� �������� ������ 
                     * �� ���� �����, ����� ���� ���������� �������
                     */

                    $cell_type = $this->spreadsheet_cell_type(
                            $row['cells'][$i]['content'], $flatten_data);

                    switch ($cell_type) {
                        case 'float':
                            /*
                             * 'office:value-type' => 'string',
                             * 'calcext:value-type' => 'string'
                             */
                            $row['cells'][$i]['attr']['office:value-type'] = 'float';
                            $row['cells'][$i]['attr']['calcext:value-type'] = 'float';

                            $field_name = $this->spreadsheet_cell_field(
                                    $row['cells'][$i]['content']);

                            if (!empty($flatten_data[$field_name])) {
                                /*
                                 * � ��� ����� ����� ������ �� ��������
                                 * �� ����� ���� ����� ��� � ��������
                                 */
                                $row['cells'][$i]['content'] = $flatten_data[$field_name];
                                $row['cells'][$i]['attr']['office:value'] = $flatten_data[$field_name];
                            } else {
                                $row['cells'][$i]['content'] = '<text:p></text:p>';
                            }

                            break;
                        default:
                            $row['cells'][$i]['content'] = String::insert(
                                            $row['cells'][$i]['content'], $flatten_data, $this->string_options
                            );
                            break;
                    }
                }

            }
        }
        return $row;
    }

    private function spreadsheet_cell_field($cell_content) {
        list($empty, $tmp) = explode('<text:p>', $cell_content);
        list($content, $empty) = explode('</text:p>', $tmp);
        $field_name = false;
        if (
                $content[0] == $this->string_options['before'] &&
                $content[strlen($content) - 1] == $this->string_options['after']
        ) {
            $field_name = substr($content, 1, strlen($content) - 2);
        }
        return $field_name;
    }

    private function spreadsheet_cell_type($cell_content, $flatten_data) {
        //'<text:p>1234</text:p>'
        $cell_type = 'string';
        $field_name = $this->spreadsheet_cell_field($cell_content);

        if (!$field_name)
            return $cell_type;

        if (!empty($flatten_data[$field_name])) {
            if (is_float($flatten_data[$field_name]))
                return 'float';

            if (is_numeric($flatten_data[$field_name]))
                return 'float';
        }

        return $cell_type;
    }

    private function spreadsheet_get_sheet_index($sheetname) {
        $sheet_ix = 0;
        foreach ($this->ods['office:spreadsheet']['table:table'] as $tbl) {
            if ($tbl['attr']['table:name'] == $sheetname)
                return $sheet_ix;
            $sheet_ix++;
        }
        return false;
    }

    private function expr_to_range_block($expr1) {

        $rb_ix = 0;
        /*
         * find block
         */
        $block_added = false;

        for ($i = 0; $i < count($this->ods['range-blocks']); $i++) {
            $rb = $this->ods['range-blocks'][$i];
            if (!in_array($expr1['name'], $rb['ranges']) && !empty($expr1['added'])) {
                /*
                 * BBBBBBBBBBBBB
                 *    EEEEEEEEEE
                 * 
                 * BBBBBBBBBBBBB
                 *    EEEE
                 */
                if (
                        $rb['start'] < $expr1['start'] &&
                        $rb['end'] >= $expr1['end']
                ) {
                    $this->ods['range-blocks'][$i]['ranges'][] = $expr1['name'];
                    $block_added = true;
                    $expr1['addes'] = true;
                }
                /*    BBBBBBBBBBBB
                 * EEEEEEEEEEEEEEE
                 * 
                 *       BBBBBBB
                 * EEEEEEEEEEEEEEE
                 */
                if (
                        $rb['start'] > $expr1['start'] &&
                        $rb['end'] >= $expr1['end']
                ) {
                    $this->ods['range-blocks'][$i]['ranges'][] = $expr1['name'];
                    $this->ods['range-blocks'][$i]['start'] = $expr1['start'];
                    $this->ods['range-blocks'][$i]['end'] = $expr1['end'];


                    $block_added = true;
                    $expr1['addes'] = true;
                }
            }
        }

        if (!$block_added) {
            /*
             * Add block
             */
            $this->ods['range-blocks'][] = array(
                'start' => $expr1['start'],
                'end' => $expr1['end'],
                'ranges' => array(
                    $expr1['name']
                )
            );
        }

        return $block_added;
    }

    private function expression_master_detail($expr1, $expr2) {
        /*
         * 11111111
         *    22222
         * 
         * 11111111
         *    2222
         */

        if (
                $expr1['start'] < $expr2['start'] &&
                $expr1['end'] >= $expr2['end']
        )
            return true;

        return false;
    }

    private function expressions_in_block($expr1, $expr2) {

        /*
         *    111111
         *  22222222
         * 
         *    11111
         *  22222222
         */
        if (
                $expr1['start'] > $expr2['start'] &&
                $expr1['end'] <= $expr2['end']
        )
            return true;

        /*
         *      11111111
         *          2222
         * 
         *      11111111
         *          222
         */

        if (
                $expr2['start'] > $expr1['start'] &&
                $expr2['end'] <= $expr1['end']
        )
            return true;

        /*
         *                      11111111
         *     222222222222
         */
        if ($expr1['start'] > $expr2['end'])
            return false;

        /*
         * 111111111
         *              2222222222
         */
        if ($expr2['start'] > $expr1['end'])
            return false;



        /*
         * default
         */
        return false;
    }

    /*
     * read structure from xml to
     */

    public function read_spreadsheet_content() {
        if (!$this->unzip())
            return false;



        $content = file_get_contents($this->content_xml);


        if (Configure::read('App.encoding') == 'CP1251') {
            // debug('content::utf8->cp1251');
            $content = iconv('utf8', 'cp1251', $content);
        }

        /*
         * Spredsheet part
         */
        list($ods['before'], $tmp) = explode('<office:spreadsheet>', $content);
        list($ods['office:spreadsheet'], $ods['after']) = explode('</office:spreadsheet>', $tmp);

        /*
         * named ranges
         */
        $ods['named-expression'] = array();

        if (strpos($ods['office:spreadsheet'], '<table:named-expressions>') !== false) {
            list($ods['table:table'], $tmp) = explode('<table:named-expressions>', $ods['office:spreadsheet']);
            $ods['table:named-expressions'] = current(explode('</table:named-expressions>', $tmp));

            $expr = explode('<table:named-range ', $ods['table:named-expressions']);
            $ods['table:named-expressions_content'] = $ods['table:named-expressions'];
            array_shift($expr);
            $ods['table:named-expressions'] = array();


            $expr_id = 1;
            foreach ($expr as $r) {
                $r = '<table:named-range ' . $r;
                $expr_tag = $this->tag_attr($r);

                if (!empty($expr_tag['attr']['table:range-usable-as'])) {
                    if ($expr_tag['attr']['table:range-usable-as'] == 'repeat-row') {
                        $expr_tag['repeat-row'] = true;

                        if (strpos($expr_tag['attr']['table:cell-range-address'], ':') === false) {
                            list($sheet, $start_cell) = explode('.', $expr_tag['attr']['table:cell-range-address']);
                            $expr_tag['attr']['table:cell-range-address'] .= ':.' . $start_cell;
                        }
                        list($sheet, $start_cell, $end_cell) = explode('.', $expr_tag['attr']['table:cell-range-address']);

                        $expr_tag['sheet'] = end(explode('$', $sheet));


                        list($_, $col, $expr_tag['start']) = explode('$', $start_cell);
                        list($_, $col, $expr_tag['end']) = explode('$', $end_cell);
                        $expr_tag['start'] = (int) current(explode(':', $expr_tag['start']));
                        $expr_tag['end'] = (int) $expr_tag['end'];
                        $expr_tag['length'] = $expr_tag['end'] - $expr_tag['start'] + 1;

                        $ods['named-expression'][$expr_tag['attr']['table:name']] = array(
                            'sheet' => $expr_tag['sheet'],
                            'name' => $expr_tag['attr']['table:name'],
                            'start' => $expr_tag['start'],
                            'end' => $expr_tag['end'],
                            'length' => $expr_tag['length'],
                            'id' => $expr_id
                        );
                        $expr_id++;
                        //$����1.$A$5:$AMJ$5
                        //table:cell-range-address
                    }
                }

                $ods['table:named-expressions'][] = $expr_tag;
            }
            // debug($expr);
        } else {
            $ods['table:table'] = $ods['office:spreadsheet'];
        }
        unset($ods['office:spreadsheet']);

        /*
         * find sheets
         */

        $tables = explode('</table:table>', $ods['table:table']);
        unset($ods['table:table']);
        array_pop($tables);

        foreach ($tables as $tbl_text) {
            list($table_tag, $table_content) = $this->extract_first_tag_str($tbl_text);
            $tbl_tag = $this->tag_attr($table_tag);
            $tbl_tag['content'] = $table_content;

            /*
             * find columns
             */

            $start_row_pos = strpos($tbl_tag['content'], '<table:table-row');

            $tbl_tag['columns'] = substr($tbl_tag['content'], 0, $start_row_pos);
            $tbl_tag['content'] = substr($tbl_tag['content'], $start_row_pos);

            $table_header_pos = strpos($tbl_tag['content'], '<table:table-header-rows>');

            if ($table_header_pos !== false) {
                list($tbl_tag['content0'], $tmp) = explode('<table:table-header-rows>', $tbl_tag['content']);
                list($tbl_tag['content1'], $tbl_tag['content2']) = explode('</table:table-header-rows>', $tmp);
            } else {
                $tbl_tag['content0'] = $tbl_tag['content'];
                $tbl_tag['content1'] = null;
                $tbl_tag['content2'] = null;
            }
            unset($tbl_tag['content']);

            /*
             * parse rows
             */
            $tbl_tag['rows'] = array();
            $tbl_tag['rows'] = array_merge($tbl_tag['rows'], $this->spreadsheet_rows($tbl_tag['content0'], 0));
            $tbl_tag['rows'] = array_merge($tbl_tag['rows'], $this->spreadsheet_rows($tbl_tag['content1'], 1)); // <---<table:table-header-rows> ZONE
            $tbl_tag['rows'] = array_merge($tbl_tag['rows'], $this->spreadsheet_rows($tbl_tag['content2'], 2));
            unset(
                    $tbl_tag['content0'], $tbl_tag['content1'], $tbl_tag['content2']
            );

            $ods['office:spreadsheet']['table:table'][] = $tbl_tag;
        }



        //debug($tables);
        $this->ods = $ods;
        //   debug($ods);
    }

    public function spreadsheet_build() {
        $content_xml[] = $this->ods['before'];
        $content_xml[] = '<office:spreadsheet>';

        foreach ($this->ods['office:spreadsheet']['table:table'] as $sheet) {
            $sheet_xml = array();
            $sheet_xml[] = $sheet['source'];
            $sheet_xml[] = $sheet['columns'];

            foreach ($sheet['rows'] as $row_object) {

                if ($row_object['content_num'] == 1 && !empty($row_object['content_first']))
                    $sheet_xml[] = '<table:table-header-rows>';


                $row_xml = array();
                $row_xml[] = $this->tag_text($row_object);

                /*
                 * cells
                 */

                foreach ($row_object['cells'] as $cell) {

                    $cell_xml = null;
                    if (isset($cell['content'])) {
                        if ($cell['content'] === false) {
                            $cell_xml = $cell['source'];
                        } else {
                            $cell_xml = $this->tag_text($cell);

                            $cell_xml .= $cell['content'];

                            $cell_xml .= '</table:table-cell>';
                        }
                    }

                    $row_xml[] = $cell_xml;


                    /*
                     * 'table:style-name' => 'ce9',
                      'office:value-type' => 'currency',
                      'office:currency' => 'RUB',
                      'office:value' => '123',
                      'calcext:value-type' => 'currency'
                      ),
                      'content' => '<text:p>123,00 ���.</text:p>'
                      ),
                      (int) 2 => array(
                      'source' => '<table:table-cell office:value-type="float" office:value="128" calcext:value-type="float">',
                      'tagName' => 'table:table-cell',
                      'attr' => array(
                      'office:value-type' => 'float',
                      'office:value' => '128',
                      'calcext:value-type' => 'float'
                      ),
                      'content' => '<text:p>128</text:p>'
                      ),
                      (int) 3 => array(
                      'source' => '<table:table-cell table:style-name="ce10" office:value-type="percentage" office:value="0.5" calcext:value-type="percentage">',
                      'tagName' => 'table:table-cell',
                      'attr' => array(
                      'table:style-name' => 'ce10',
                      'office:value-type' => 'percentage',
                      'office:value' => '0.5',
                      'calcext:value-type' => 'percentage'
                     * 
                     * 'attr' => array(
                      'table:style-name' => 'ce4',
                      'office:value-type' => 'string',
                      'calcext:value-type' => 'string'
                      ),
                      'content' => '<text:p>[Document.name] �� [Document.date]</text:p>'
                     */
                }

                $row_xml[] = '</table:table-row>';

                $sheet_xml = array_merge($sheet_xml, $row_xml);




                if ($row_object['content_num'] == 1 && !empty($row_object['content_last']))
                    $sheet_xml[] = '</table:table-header-rows>';
            }

            /*
             * rows
             */

            $sheet_xml[] = '</table:table>';
            $content_xml = array_merge($content_xml, $sheet_xml);
        }

        $content_xml[] = $this->ods['table:named-expressions_content'];
        $content_xml[] = '</office:spreadsheet>';
        $content_xml[] = $this->ods['after'];
        return implode($content_xml);
    }

    /*
     * Analyze xml content in the <table:table></table:table>
     */

    private function spreadsheet_rows($string, $content_num) {
        $rows1 = explode('</table:table-row>', $string);
        array_pop($rows1);

        $result_rows = array();
        $ix = 0;
        foreach ($rows1 as $row_text) {
            list($row_text, $cellscontent) = $this->extract_first_tag_str($row_text);
            $row_tag = $this->tag_attr($row_text);

            $row_tag['content'] = explode('<table:table-cell', $cellscontent);

            /*
             * if cell is spanned columns or rows
             */
            if ($row_tag['content']) {
                if ($row_tag['content'][0] == '') {
                    array_shift($row_tag['content']);
                }
            }

            foreach ($row_tag['content'] as $cell_content) {

                if (strpos($cell_content, '<table:covered-table-cell') === 0) {
                    /*
                     * Compensation cell before
                     */

                    $covered_list = explode('<table:covered-table-cell', $cell_content);

                    array_shift($covered_list);
                    foreach ($covered_list as $c) {
                        $coverTag = $this->tag_attr('<table:covered-table-cell' . $c);
                        $coverTag['content'] = false;
                        $row_tag['cells'][] = $coverTag;
                    }
                }

                if ($cell_content[0] == ' ' || $cell_content[0] == '/') {
                    /*
                     * ������� ������
                     */
                    $cell_content = '<table:table-cell' . $cell_content;
                    $cell_closing_pair_pos = strpos($cell_content, '</table:table-cell>');

                    if ($cell_closing_pair_pos !== false) {
                        /*
                         * cell is content
                         */
                        list($cell_tag_text, $cell_tag_content) = $this->extract_first_tag_str($cell_content);
                        $cell_tag = $this->tag_attr($cell_tag_text);
                        $cell_tag['content'] = current(explode('</table:table-cell>', $cell_tag_content));
                    } else {
                        $cell_tag = $this->tag_attr($cell_content);
                        $cell_tag['content'] = false;
                    }
                    $row_tag['cells'][] = $cell_tag;

                    /*
                     * Check compensation cells after cell
                     */
                    if (strpos($cell_content, '<table:covered-table-cell') !== false) {
                        /*
                         * Compensation cell before
                         */
                        $covered_list = explode('<table:covered-table-cell', $cell_content);

                        array_shift($covered_list);
                        foreach ($covered_list as $c) {
                            $coverTag = $this->tag_attr('<table:covered-table-cell' . $c);
                            $coverTag['content'] = false;
                            $row_tag['cells'][] = $coverTag;
                        }
                    }
                }
            }
            unset($row_tag['content']);
            $row_tag['content_num'] = $content_num;
            $row_tag['content_index'] = $ix;
            if ($ix == 0)
                $row_tag['content_first'] = true;
            if ($ix == count($rows1) - 1)
                $row_tag['content_last'] = true;

            $row_tag['repeated'] = false;
            if (empty($row_tag['attr']['table:number-rows-repeated']))
                $result_rows[] = $row_tag;
            else {
                $repeated = (int) $row_tag['attr']['table:number-rows-repeated'];
                $row_tag['repeated'] = true;
                unset($row_tag['attr']['table:number-rows-repeated']);
                for ($i = 0; $i < $repeated; $i++)
                    $result_rows[] = $row_tag;
            }
            
            $ix++;
        }
        return $result_rows;
    }

    private function extract_first_tag_str($string) {
        $start_pos = strpos($string, '<');
        $end_pos = strpos($string, '>');
        if ($start_pos === false || $end_pos === false)
            return false;

        if ($start_pos >= $end_pos)
            return false;

        return
                array(
                    substr($string, $start_pos, $end_pos - $start_pos + 1),
                    substr($string, $end_pos + 1)
        );
    }

    function tag_text($tag, $options = array()) {
        $t = '<' . $tag['tagName'];

        if (!empty($tag['attr'])) {
            foreach ($tag['attr'] as $attr => $value) {
                $t .= " $attr=\"$value\"";
            }
        }

        if (isset($tag['content'])) {
            if ($tag['content'] === false)
                $t .= '/>';
            else {
                $t .= '>';
            }
        } else {
            $t .= '>';
        }

        return $t;
    }

    function tag_attr($string) {
        $tag['start'] = strpos($string, '<') + 1;
        $tag['end'] = strpos($string, '>');
        $tag['source'] = $string;

        if ($string[$tag['end'] - 1] == '/') {
            $tag['once'] = true;
            $tag['end'] -= 1;
        }
        $tag['len'] = $tag['end'] - $tag['start'];

        $tag_a = substr($string, $tag['start'], $tag['len']);

        $tag['words'] = explode(' ', $tag_a);
        $tag['tagName'] = array_shift($tag['words']);
        $tag['attr'] = array();
        foreach ($tag['words'] as $attr_str) {
            list($attr_name, $attr_value) = explode('=', $attr_str);
            $tag['attr'][$attr_name] = substr($attr_value, 1, strlen($attr_value) - 2);
        }
        foreach ($tag['attr'] as $attr => $value) {
            if (strpos($attr, 'xmlns:') !== false) {
                $ns = end(explode('xmlns:', $attr));
                $tag['xmlns'][$ns] = $value;
            }
        }

        unset(
                $tag['start'], $tag['end'], $tag['len'], $tag['words']
        );

        return $tag;
    }

    private function ods_render_rows($rows_xml, $data, $options = array()) {
        $result_rows_xml = array();

        $flatten_data = Hash::flatten($data);

        foreach ($rows_xml as $row_xml) {
            $row_xml = String::insert($row_xml, $flatten_data, $this->string_options);

            if (strpos($row_xml, '!!{') !== false) {

                $data1_key = current(explode('}!!', end(explode('!!{', $row_xml))));

                if (!empty($data[$data1_key])) {
                    $number = 1;
                    foreach ($data[$data1_key] as $datum1) {
                        $flatten_datum = Hash::flatten($datum1);
                        $flatten_datum['pos_number'] = $number;

                        $result_rows_xml[] = String::insert($row_xml, array_merge($flatten_data, $flatten_datum));
                        $number++;
                    }
                }

            } else
            if (strpos($row_xml, '!!!!TBODY33') !== false) {
                $data1_key = 'DocumentDatum';
                if (!empty($data[$data1_key])) {
                    $number = 1;
                    foreach ($data[$data1_key] as $datum1) {
                        $flatten_datum = Hash::flatten($datum1);
                        $flatten_datum['number'] = $number;
                        $result_rows_xml[] = String::insert($row_xml, array_merge($flatten_data, $flatten_datum));
                        $number++;
                    }
                }
            } else
                $result_rows_xml[] = $row_xml;
        }

        return $result_rows_xml;
    }

    private function add_global_vars_to_data() {
        $this->data['Report']['time'] = date('H:i:s');
        $this->data['Report']['date'] = date('d.m.Y');
        $this->data['Report']['user'] = Configure::read('login_username') ? Configure::read('login_username') : $this->userProfile;
    }

    private function text_document_render(){
        if (!$this->unzip())
            return false;
        
        $content = file_get_contents($this->content_xml);

        if (Configure::read('App.encoding') == 'CP1251') {
            $content = iconv('utf8', 'cp1251', $content);
        }
        
        $this->data_for_body_flatten = Hash::flatten($this->data);
        
        return String::insert($content, $this->data_for_body_flatten, $this->string_options);
    }
    
    public function odt($odt_template_file = null, $result_file, $data, $options = array()) {
        $this->filename = $odt_template_file;
        $this->data = $data;
        $this->add_global_vars_to_data();

        $this->read_meta();
        
        $content_xml = $this->text_document_render();

        if (Configure::read('App.encoding') == 'CP1251')
            $content_xml = iconv('cp1251', 'utf8', $content_xml);

        file_put_contents($this->content_dir . DS . 'out' . DS . 'content.xml', $content_xml);
        /*
         * work on styles.xml
         */
        if (!empty($this->data_for_body_flatten)) {
            $styles_xml = file_get_contents($this->styles_xml);
            if (Configure::read('App.encoding') == 'CP1251')
            {
                /*
                 * ������ ����� �����
                 *  text:bullet-char="?"
                 */
                $styles_xml = str_replace(chr(239) . chr(130) .  chr(183), '***', $styles_xml);
                $styles_xml = iconv('utf8', 'cp1251', $styles_xml);
            }

            $styles_xml = String::insert($styles_xml, $this->data_for_body_flatten, $this->string_options);

            if (Configure::read('App.encoding') == 'CP1251')
                $styles_xml = iconv('cp1251', 'utf8', $styles_xml);

            file_put_contents($this->content_dir . DS . 'out' . DS . 'styles.xml', $styles_xml);
        }
        $this->zip($result_file);        
    }
    public function ods($ods_template_file = null, $result_file, $data, $options = array()) {
        $this->filename = $ods_template_file;
        $this->data = $data;
        $this->add_global_vars_to_data();

        $this->read_meta();

        $this->read_spreadsheet_content();
        $this->spreadsheet_render();

        $content_xml = $this->spreadsheet_build();

        if (Configure::read('App.encoding') == 'CP1251')
            $content_xml = iconv('cp1251', 'utf8', $content_xml);

        file_put_contents($this->content_dir . DS . 'out' . DS . 'content.xml', $content_xml);
        /*
         * work on styles.xml
         */
        if (!empty($this->data_for_body_flatten)) {
            $styles_xml = file_get_contents($this->styles_xml);
            if (Configure::read('App.encoding') == 'CP1251')
                $styles_xml = iconv('utf8', 'cp1251', $styles_xml);

            $styles_xml = String::insert($styles_xml, $this->data_for_body_flatten, $this->string_options);

            if (Configure::read('App.encoding') == 'CP1251')
                $styles_xml = iconv('cp1251', 'utf8', $styles_xml);

            file_put_contents($this->content_dir . DS . 'out' . DS . 'styles.xml', $styles_xml);
        }
        $this->zip($result_file);
    }
}

/*

 ODT/meta.xls
 * 
<?xml version="1.0" encoding="UTF-8"?>
<office:document-meta xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:grddl="http://www.w3.org/2003/g/data-view#" office:version="1.2">
    <office:meta>
        <meta:creation-date>2014-09-06T10:30:01.032307060</meta:creation-date>
        <dc:date>2014-09-06T12:47:51.110928325</dc:date>
        <meta:editing-duration>P0D</meta:editing-duration>
        <meta:editing-cycles>4</meta:editing-cycles>
        <meta:generator>LibreOffice/4.2.6.3$Linux_X86_64 LibreOffice_project/420m0$Build-3</meta:generator>
        <meta:document-statistic meta:table-count="1" meta:image-count="1" meta:object-count="0" meta:page-count="3" meta:paragraph-count="7" meta:word-count="9" meta:character-count="73" meta:non-whitespace-character-count="71"/>
        <meta:user-defined meta:name="Option.bool_false" meta:value-type="boolean">false</meta:user-defined>
        <meta:user-defined meta:name="Option.bool_true" meta:value-type="boolean">true</meta:user-defined>
        <meta:user-defined meta:name="Option.datetime" meta:value-type="date">2014-04-06</meta:user-defined>
        <meta:user-defined meta:name="Option.datetime_type" meta:value-type="date">2014-09-06T06:03:02</meta:user-defined>
        <meta:user-defined meta:name="Option.number_type" meta:value-type="float">123</meta:user-defined>
        <meta:user-defined meta:name="Option::continue" meta:value-type="time">P5MT12M</meta:user-defined>
    </office:meta>
</office:document-meta>
 */