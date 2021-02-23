export default {
  widgets: [
    {
      name: 'sanity-tutorials',
      options: {
        templateRepoId: 'sanity-io/sanity-template-gridsome-blog'
      }
    },
    {name: 'structure-menu'},
    {
      name: 'project-info',
      options: {
        __experimental_before: [
          {
            name: 'netlify',
            options: {
              description:
                'NOTE: Because these sites are static builds, they need to be re-deployed to see the changes when documents are published.',
              sites: [
                {
                  buildHookId: '60350510667c531882f6f8a1',
                  title: 'Sanity Studio',
                  name: 'schwedenbutze-studio',
                  apiId: '7eed8d89-5e9d-473d-bb95-cb62672fa7cd'
                },
                {
                  buildHookId: '60350510b8b2f1125ed3c9db',
                  title: 'Blog Website',
                  name: 'schwedenbutze',
                  apiId: 'a9ad9239-4922-4146-b526-60c12b2823c7'
                }
              ]
            }
          }
        ],
        data: [
          {
            title: 'GitHub repo',
            value: 'https://github.com/cwmoss/schwedenbutze',
            category: 'Code'
          },
          {title: 'Frontend', value: 'https://schwedenbutze.netlify.app', category: 'apps'}
        ]
      }
    },
    {name: 'project-users', layout: {height: 'auto'}},
    {
      name: 'document-list',
      options: {title: 'Recent blog posts', order: '_createdAt desc', types: ['post']},
      layout: {width: 'medium'}
    }
  ]
}
