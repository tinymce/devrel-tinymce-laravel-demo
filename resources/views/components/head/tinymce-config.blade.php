@php
    $apiKey = env('VITE_TINYMCE_API_KEY');
@endphp
<script src="https://cdn.tiny.cloud/1/{{ $apiKey }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: 'textarea#myeditorinstance',
    plugins: 'ai code table lists',
    toolbar: 'undo redo | blocks | formatselect | ' +
      'bold italic backcolor | alignleft aligncenter ' +
      'alignright alignjustify | bullist numlist outdent indent | ' +
      'removeformat | aidialog aishortcuts | help',
    height: 500,
    promotion: false,
    ai_request: (request, respondWith) => {
      respondWith.string(async (signal) => {
        try {
          const response = await fetch('/ai/stream', {
            signal,
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              thread: request.thread,
              system: request.system,
              query: request.query,
              context: request.context
            })
          });

          if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || errorData.message || 'Network response was not ok');
          }

          const data = await response.json();
          if (data.error) {
            throw new Error(data.error);
          }

          return data.content;
        } catch (error) {
          console.error('AI request error:', error);
          throw error;
        }
      });
    },
    setup: function(editor) {
      editor.on('init', function() {
        console.log('TinyMCE initialized');
      });
      editor.on('error', function(e) {
        console.error('TinyMCE error:', e);
      });
    }
  });
</script>