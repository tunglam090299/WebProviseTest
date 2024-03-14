<?php

class Travel
{
    // Enter your code here
    public function getData()
    {
        $api_url = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';
        $json_data = file_get_contents($api_url);

        $response_data = json_decode($json_data);

        return $response_data;
    }
}

class Company
{
    // Enter your code here
    public function getData()
    {
        $api_url = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';
        $json_data = file_get_contents($api_url);

        $response_data = json_decode($json_data);

        return $response_data;
    }
}
// array used for pushing parentId in
$parents = [];

class TestScript
{
    protected $company;
    protected $travel;

    public function __construct(Company $company,Travel $travel)
    {
        $this->company = $company;
        $this->travel = $travel;
    }

    public function getTotalCostEachCompany()
    {
        $list_travel = $this->travel->getData();
        $list_company = $this->company->getData();
        $list_company_with_cost = [];
        // Calculate cost of each company which match the employees companyId
        foreach ($list_company as $company) {
            $company->cost = 0;
            foreach ($list_travel as $item) {
                if ($item->companyId == $company->id) {
                    $company->cost += (int)strtok($item->price, '.');
                }
            }
            $list_company_with_cost[$company->id] = $company;
        }

        return $list_company_with_cost;
    }

    public function findParent($array, $id){
        global $parents;
        // loop to get all parents
        if(is_array($array)) {
            foreach($array as $item) {
                if($item->id == $id ) {
                    array_push($parents, $item->id);
                    if($item->parentId){
                        $this->findParent($array, $item->parentId);
                    }
                }
            }
        }
        
    }

    public function formatListCompanyWithChildren($list_company){
        global $parents;
        $list_company_with_child = [];
        foreach ($list_company as $parent){
            $parent->children = [];
            //check the head company
            if(!$parent->parentId){
                $head_company = $parent;
            }
            foreach ($list_company as $child){
                if($child->parentId == $parent->id) {
                    // add cost into the parent company
                    $parents = [];
                    $this->findParent($list_company, $child->parentId);
                    foreach($parents as $id){
                        $list_company[$id]->cost += $child->cost;
                    }

                    array_push($parent->children, $child);
                }
            }
            array_push($list_company_with_child, $parent);
        }

        foreach($list_company_with_child as $index => $item){
            // keep only the total of head company
            if($item->parentId){
                unset($list_company_with_child[$index]);
            }
        }
        return $list_company_with_child;
    }

    public function execute()
    {
        $list_company = $this->getTotalCostEachCompany();
        $list_company_with_child = $this->formatListCompanyWithChildren($list_company);
        print_r(json_encode($list_company_with_child));
    }
}
$company = new Company();
$travel = new Travel();
(new TestScript($company, $travel))->execute();
