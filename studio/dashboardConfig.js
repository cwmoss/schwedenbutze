import { netlifyWidget } from 'sanity-plugin-dashboard-widget-netlify'

export default {
  widgets: [
    netlifyWidget({
      title: 'Netlify deploys',
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
    }),
    {
      name: 'project-info',

      options: {
        data: [
          {
            title: 'GitHub repo',
            value: 'https://github.com/cwmoss/schwedenbutze',
            category: 'Code'
          },
          { title: 'Frontend', value: 'https://schwedenbutze.netlify.app', category: 'apps' }
        ]
      }
    },
    { name: 'project-users', layout: { height: 'auto' } },
    {
      name: 'document-list',
      options: { title: 'Recent blog posts', order: '_createdAt desc', types: ['post'] },
      layout: { width: 'medium' }
    }
  ]
}
