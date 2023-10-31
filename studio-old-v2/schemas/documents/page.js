export default {
  name: 'page',
  title: 'Page',
  type: 'document',
  fields: [
    {
      name: 'title',
      title: 'Title',
      type: 'string'
    },
    {
      name: 'slug',
      title: 'Slug',
      type: 'slug',
      options: {
        source: 'title',
        maxLength: 96
      }
    },
    {
      name: 'mainImage',
      title: 'Main image',
      type: 'image',
      options: {
        hotspot: true
      }
    },
    {
      title: "Sections",
      name: "sections",
      type: 'array',
      of: [
        {
          type: "section",
        }
      ],
      options:{
        // sortable: false,
         //editModal: 'popover'
        // layout: 'grid'
      }
    }
  ]
}
