@php
    $apiKey = env('VITE_TINYMCE_API_KEY');
@endphp
<script src="https://cdn.tiny.cloud/1/{{ $apiKey }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: 'textarea#myeditorinstance',
    plugins: [
      'a11ychecker','advlist','advcode','advtable','autolink','checklist','markdown',
      'lists','link','image','charmap','preview','anchor','searchreplace','visualblocks',
      'powerpaste','fullscreen','formatpainter','insertdatetime','media','table','help','wordcount',
      'mergetags', 'advtemplate', 'ai'
    ],
    toolbar: 'undo redo | aidialog aishortcuts | casechange blocks | formatting | bullist numlist checklist outdent indent | mergetags inserttemplate | a11ycheck code table help',
    height: 500,
    promotion: false,
    menubar: false,
    statusbar: false,
    toolbar_groups: {
      formatting: {
        icon: 'format',
        tooltip: 'Formatting',
        items: 'bold italic underline strikethrough | forecolor backcolor | superscript subscript | alignleft aligncenter alignright alignjustify | removeformat'
      }
    },
    toolbar_location: 'bottom',
    advcode_inline: true,
    ai_request: (request, respondWith) => {
      respondWith.string(async (signal) => {
        try {
          const response = await fetch('http://localhost:11434/v1/chat/completions', {
            signal,
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              messages: [
                {
                  role: "user",
                  content: request.query
                }
              ],
              model: "deepseek-r1:1.5b",
              stream: false
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

          return data.choices[0].message.content;
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
    },
    mergetags_list: [
      {
        title: "Contact",
        menu: [{
          value: 'Contact.FirstName',
          title: 'Contact First Name'
        },
        {
          value: 'Contact.LastName',
          title: 'Contact Last Name'
        },
        {
          value: 'Contact.Email',
          title: 'Contact Email'
        }
        ]
      },
      {
        title: "Sender",
        menu: [{
          value: 'Sender.FirstName',
          title: 'Sender First Name'
        },
        {
          value: 'Sender.LastName',
          title: 'Sender Last name'
        },
        {
          value: 'Sender.Email',
          title: 'Sender Email'
        }
        ]
      },
      {
        title: 'Subscription',
        menu: [{
          value: 'Subscription.UnsubscribeLink',
          title: 'Unsubscribe Link'
        },
        {
          value: 'Subscription.Preferences',
          title: 'Subscription Preferences'
        }
        ]
      }
    ],
    advtemplate_templates: [
      {
        title: "Newsletter intro",
        content:
          '<h1 style="font-size: 24px; color: rgb(51, 93, 255); font-family:Arial;">TinyMCE Newsletter</h1>\n<p style="font-family:Arial;">Welcome to your monthly digest of all things TinyMCE, where you\'ll find helpful tips, how-tos, and stories of how people are using rich text editing to bring their apps to new heights!</p>',
      },
      {
        title: "CTA Button",
        content:
          '<p><a style="background-color: rgb(51, 93, 255); padding: 12px 16px; color: rgb(255, 255, 255); border-radius: 4px; text-decoration: none; display: inline-block; font-family:Arial;" href="https://tiny.cloud/pricing">Get started with your 14-day free trial</a></p>',
      },
      {
        title: "Footer",
        content:
          '<p style="text-align: center; font-size: 10px; font-family:Arial;">You received this email at because you previously subscribed.</p>\n<p style="text-align: center; font-size: 10px; font-family:Arial;"></p>',
      },
    ],
  });
</script>