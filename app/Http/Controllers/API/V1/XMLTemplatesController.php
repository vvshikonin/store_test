<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V1\XMLTemplate;
use Artisan;
use Storage;

class XMLTemplatesController extends Controller
{
    /**
     * Отображает список всех XML-шаблонов.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $templates = XMLTemplate::all();
        return response()->json($templates);
    }

    /**
     * Запускает ручную генерацию XML-каталогов.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manual_generate(Request $request)
    {
        Artisan::call('run:generate-xml-catalog');

        return response()->json(['message' => 'Каталоги XML успешно сгенерированы.']);
    }

    /**
     * Сохраняет новый XML-шаблон.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'brand_ids' => 'required|array',
            'brand_ids.*' => 'integer|exists:brands,id', // Проверка, что каждый brand_id существует
        ]);

        $template = XMLTemplate::create($validatedData);

        return response()->json([
            'message' => 'Шаблон XML успешно создан.',
            'template' => $template
        ]);
    }

    /**
     * Обновляет существующий XML-шаблон.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\V1\XMLTemplate $xmlTemplate
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, XMLTemplate $xmlTemplate)
    {
        $oldXmlLink = $xmlTemplate->xml_link;
    
        $xmlTemplate->fill($request->all());
        $xmlTemplate->save();
    
        if ($oldXmlLink && $oldXmlLink !== $xmlTemplate->xml_link) {
            $this->deleteXmlFile($oldXmlLink);
        }
    
        return response()->json([
            'message' => 'Шаблон XML успешно обновлен.',
            'template' => $xmlTemplate->refresh()
        ]);
    }
    

    /**
     * Удаляет существующий XML-шаблон.
     *
     * @param \App\Models\V1\XMLTemplate $xmlTemplate
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(XMLTemplate $xmlTemplate)
    {
        $xmlLink = $xmlTemplate->xml_link;
    
        $xmlTemplate->delete();
    
        if ($xmlLink) {
            $this->deleteXmlFile($xmlLink);
        }
    
        return response()->json([
            'message' => 'Шаблон XML успешно удален.'
        ]);
    }

    /**
     * Удаляет XML-файл из файловой системы, если он существует.
     *
     * @param string $xmlLink Относительный путь к файлу XML, который нужно удалить.
     * @return void
     */
    protected function deleteXmlFile($xmlLink)
    {
        if (Storage::disk('public')->exists($xmlLink)) {
            Storage::disk('public')->delete($xmlLink);
        }
    }
}
