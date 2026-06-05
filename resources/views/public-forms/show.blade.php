<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $form->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $form->title }}</h1>

        <form action="{{ route('public-forms.submit', $form->embed_code) }}" method="POST">
            @csrf
            
            @foreach($form->fields as $field)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $field['label'] }}
                        @if($field['required'] ?? false)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    
                    @if($field['type'] === 'textarea')
                        <textarea name="field_{{ $field['id'] ?? Str::slug($field['label']) }}" 
                                 class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                 {{ ($field['required'] ?? false) ? 'required' : '' }}></textarea>
                    @elseif($field['type'] === 'select')
                        <select name="field_{{ $field['id'] ?? Str::slug($field['label']) }}" 
                                class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            <option value="">Selecione</option>
                            @foreach($field['options'] ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="{{ $field['type'] }}" 
                               name="field_{{ $field['id'] ?? Str::slug($field['label']) }}" 
                               class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                               {{ ($field['required'] ?? false) ? 'required' : '' }}>
                    @endif
                </div>
            @endforeach

            <div class="mb-4">
                <div class="g-recaptcha" data-sitekey="{{ config('captcha.sitekey') }}"></div>
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Enviar
            </button>
        </form>
    </div>
</body>
</html>