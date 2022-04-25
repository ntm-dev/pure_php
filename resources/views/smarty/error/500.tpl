{{extends file="./master.tpl"}}

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
        output: '<span class="red">{{$status}} {{$message}}</span><br>&nbsp;',
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
