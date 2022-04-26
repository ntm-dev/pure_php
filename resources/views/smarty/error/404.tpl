{{extends file="./master.tpl"}}
{{if !isset($status)}}
    {{assign var="status" value=404}}
{{/if}}
{{if !isset($message)}}
    {{assign var="message" value="Page Not Found."}}
{{/if}}
{{block name=title}}{{$message}}{{/block}}<!--Sorry, we're down for essential maintenance-->
{{block name=data}}
    var data = [{
        action: 'type',
        strings: ["{{$smarty.server.REQUEST_URI}}"],
        output: '',
        postDelay: 10
    },
    {
        action: 'type',
        strings: [""],
        output: '<br><span class="red">{{$status}} {{$message}}</span><br>&nbsp;',
        postDelay: 500
    }
    //,{
    //    action: 'type',
    //    strings: ["{{$status}} Service Unavailable"],
    //    output: '<span class="info">Sorry, we\'re down for essential maintenance.</span><br>&nbsp;',
    //    postDelay: 500
    //},
    //{
    //    action: 'type',
    //    strings: ["These are not the error codes you're looking for.",
    //        'I am working on that service to make it better, faster, stronger...'
    //    ],
    //    postDelay: 2000,
    //    output: ['<span class="warning">This service will be back soon!</span>',
    //        '<span class="gray">Maintenance by The Manh.</span><br>&nbsp;'
    //    ].join('<br>'),
    //}
];
{{/block}}
