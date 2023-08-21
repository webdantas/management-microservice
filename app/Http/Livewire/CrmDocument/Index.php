<?php

namespace App\Http\Livewire\CrmDocument;

use App\Http\Livewire\WithConfirmation;
use App\Http\Livewire\WithSorting;
use App\Models\CrmDocument;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;

class Index extends Component
{
    use WithPagination;
    use WithSorting;
    use WithConfirmation;

    public int $perPage;

    public array $orderable;

    public string $search = '';

    public array $selected = [];

    public array $paginationOptions;

    protected $queryString = [
        'search' => [
            'except' => '',
        ],
        'sortBy' => [
            'except' => 'id',
        ],
        'sortDirection' => [
            'except' => 'desc',
        ],
    ];

    public function getSelectedCountProperty()
    {
        return count($this->selected);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetSelected()
    {
        $this->selected = [];
    }

    public $apiData = [];

    public function mount()
    {
        $this->sortBy            = 'id';
        $this->sortDirection     = 'desc';
        $this->perPage           = 100;
        $this->paginationOptions = config('project.pagination.options');
        $this->orderable         = (new CrmDocument())->orderable;

        $apiUrl = 'http://127.0.0.1:8989/admin/crm-documents'; // URL da API

        try {
            $response = Http::get($apiUrl);
            $this->apiData = $response->json();
        } catch (\Exception $e) {
            // Lidar com o erro, como exibir uma mensagem ou log
            $this->apiData = [];
//            echo "Erro ao acessar a API: " . $e->getMessage();
        }
    }

    public function render()
    {
        $query = CrmDocument::with(['customer'])->advancedFilter([
            's'               => $this->search ?: null,
            'order_column'    => $this->sortBy,
            'order_direction' => $this->sortDirection,
        ]);

        $crmDocuments = $query->paginate($this->perPage);

        return view('livewire.crm-document.index', compact('crmDocuments', 'query'));
    }

    public function deleteSelected()
    {
        abort_if(Gate::denies('crm_document_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        CrmDocument::whereIn('id', $this->selected)->delete();

        $this->resetSelected();
    }

    public function delete(CrmDocument $crmDocument)
    {
        abort_if(Gate::denies('crm_document_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $crmDocument->delete();
    }
}
