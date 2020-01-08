<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

## 1º Parte: Arquitetura API (Laravel)

1-Intalação projeto Laravel 5.7

    composer create-project --prefer-dist laravel/laravel blog "5.7.*"

2-Alterar timezone em config/app.php

    'timezone' => 'America/Sao_Paulo',

3-Criar Model/Migration de Category

    php artisan make:model Models\\Category -m

4-Adiciona coluna no migrate Category, adiciona no AppServiceProvider.php dentro do metodo  boot().

    Schema::defaultStringLength(191); 

Configura banco .env e roda migration

    php artisan migrate

5-Criar controller

    php artisan make:controller Api\\CategoryController

6-Cria metodo Index em CategoryController

    public function index(Category $category)
    {
        $categories = $category->all();

        return response()->json($categories, 200);
    }

Define rota do tipo GET em routes/api.php

    Route::get('categories', 'Api\CategoryController@index');

7-Faz insert manual no banco de veja o resultado (http://127.0.0.1:8000/api/categories).

8-Altera index, passando resposábilidade para model Category.

    public function index(Category $category, Request $request)
    {
        $categories = $category->getResults($request->name);

        return response()->json($categories, 200);
    }

9-Adiciona método na model Category

    public function getResults($name = null)
    {   // verifica se está passando nome na pesquisa, Se sim traz tudo!
        if (!$name)
            return $this->get();

        // Se não, faz o like
        return $this->where('name', 'LIKE', "%{$name}%")
                ->get();
    }

Faça teste no browser ou postman passando filtro com ou sem nome

    http://127.0.0.1:8000/api/categories?name=test

    http://127.0.0.1:8000/api/categories

<br>
<b>INSERT Category</b>    

10-Adicione método construtor no controller CategoryController.

    // Propriedade category usando no construct
    private $category;
    
    // Criado construct para injetar Category automaticamente
    public function __construct(Category $category)
    {   //propriedade $category recebe objeto Category
        $this->category = $category;
    }

10.1-Adicione método store

    public function store(Request $request)
    {
        $category = $this->category->create($request->all());

        return response()->json($category, 201);
    }

10.2-Altera linha para receber propriedade '$this->category' criada no construct que recebe objeto.

    $categories = $this->category->getResults($request->name);

10.3-Adicione rota do tipo POST para store()

    Route::post('categories', 'Api\CategoryController@store');

10.4-Adicione fillable no model Category para permitir o insert

    protected $fillable = ['name'];

Faça teste de insert pelo postman (http://127.0.0.1:8000/api/categories?name=Nova Categoria)

<br>
<b>EDITAR Category</b>

11-Adiciona método update() em CategoryController

    public function update(Request $request, $id)
    {
        $category = $this->category->find($id);
        if(!$category)
            return response()->json(['error' => 'Not found'], 404);
        
        $category->update($request->all());

        return response()->json($category);
    }

11.1-Adiciona rota do tipo PUT

    Route::put('categories/{id}', 'Api\CategoryController@update');


<br>
<b>VALIDAÇÃO Category</b>

12-Criar formRequest, após criar estará disponível em app\Http\Request

    php artisan make:request StoreUpdateCategoryFormRequest

12.1-Primeiro passo, passar o authorize() para true

    public function authorize()
    {
        return true;
    }

12.2-Definir regras de validações. Informar que o campo 'name' vai ser requerido, minimo 3 caracteres, maximo 50 caracteres e unico na tabela categories.

    public function rules()
    {
        return [
            'name' => 'required|min:3|max:50|unique:categories',
        ];
    }

12.3-No método store() mudar parametro Request para o StoreUpdateCategoryFormRequest criado. Não esqueça de importar (use App\Http\Requests\StoreUpdateCategoryFormRequest;)

    public function store(StoreUpdateCategoryFormRequest $request)
    {
        $category = $this->category->create($request->all());

        return response()->json($category, 201);
    }

<br>
<b>Permitir Editar registro cuja informações são unicas no banco de dados.</b>

13-Altere o parametro Request para StoreUpdateCategoryFormRequest no método update()

    public function update(StoreUpdateCategoryFormRequest $request, $id)
    {
        ...

13.1-Altere o StoreUpdateCategoryFormRequest para que quando o valor for o mesmo o laravel permite alterar.

    public function rules()
    {
        return [
            'name' => "required|min:3|max:50|unique:categories,name,{$this->segment(3)},id",
        ...

<br>
<b>DELETE Category</b>

14-Criar método delete()

    public function delete($id)
    {
        $category = Category::find($id);
        if(!$category)
            return response()->json(['error' => 'Not found'], 404);

        $category->delete();
        
        return response()->json(['success' => true], 204);
    }

Observação: usado model 'Category::find($id)' ao invez do '$this->category->find($id)'.


14.1-Adiciona rota do tipo DELETE

    Route::delete('categories/{id}', 'Api\CategoryController@delete');


15-Comente as rotas já criada e adicione Rota API Simplificada (index, store, update, destroy).

    Route::apiResource('categories', 'Api\CategoryController');
    

## 2º Parte: Autenticação JWT Laravel
## 3º Parte: Laravel + VueJs

