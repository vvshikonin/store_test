<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserFilterTemplateResource;
use Illuminate\Http\Request;
use App\Models\V1\UserFilterTemplate;

class UserFilterTemplateController extends Controller
{
    /**
     * Делает выборку всех шаблонов, принадлежащих пользователю и прикрепленных к конкретной таблице
     *
     * @param  \Illuminate\Http\Request $request
     * @return App\Http\Resources\V1\UserFilterTemplateResource
     */
    public function index(Request $request)
    {
        $templates = UserFilterTemplate::where('user_id', auth()->user()->id)
                    ->where('table', $request->get('table_enum'))
                    ->get();

        return UserFilterTemplateResource::collection($templates);
    }

    /**
     * Создает новый шаблон фильтров, прикрепляет его к пользователю,
     * который вызвал метод, и к таблице, из которой был вызван метод
     *
     * @param  \Illuminate\Http\Request $request
     * @return App\Models\V1\UserFilterTemplate $template
     */
    public function store(Request $request)
    {
        $template = UserFilterTemplate::create([
            'user_id' => auth()->user()->id,
            'name' => $request->get('name'),
            'table' => $request->get('table_enum'),
            'template_data' => $request->get('template_data')
        ]);

        return $template;
    }

    /**
     * Изменяет название шаблона фильтров
     *
     * @param  App\Models\V1\UserFilterTemplate $userFilterTemplate
     * @return App\Models\V1\UserFilterTemplate $userFilterTemplate
     */
    public function update(Request $request, UserFilterTemplate $userFilterTemplate)
    {
        $userFilterTemplate->name = $request->get('name');
        $userFilterTemplate->save();

        return $userFilterTemplate;
    }

    /**
     * Удаляет шаблон фильтров
     *
     * @param  App\Models\V1\UserFilterTemplate $userFilterTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserFilterTemplate $userFilterTemplate)
    {
        $userFilterTemplate->delete();

        return response()->json(['message' => 'Шаблон фильтров успешно удален.']);
    }
}
