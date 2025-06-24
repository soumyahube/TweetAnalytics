
import pandas as pd
import json

# Exemples de tweets neutres
neutral_examples = [
"Facebook is a social networking platform used by millions of people.",
"Users can share posts, photos, and videos.",
"Facebook helps people stay connected with friends and family."
"The platform offers groups to discuss shared interests.",
"Businesses use Facebook to promote their products and services.",
"Facebook ads can target specific audiences."
"Facebook Messenger allows sending private messages.",
"Events on Facebook help organize gatherings.",
"The 'Like' button lets users express appreciation for content.",
"Privacy settings control who can see posts."
"Facebook was founded in 2004 by Mark Zuckerberg.",
"The platform is available on desktop and mobile apps."
"Users can follow news or hobby pages.",
"Facebook Marketplace allows buying and selling items locally.",
"Stories let users share ephemeral moments.",
"Reactions (like, love, haha, etc.) offer more options than just 'Like'.",
"Comments under posts enable discussions.",
"Facebook Watch features videos and online shows.",
"Fact-checking is used to combat misinformation.",
"Facebook's algorithms personalize the news feed.",
"Live streaming lets users broadcast to followers in real-time.",
"Users can report inappropriate content.",
"Facebook works with moderators to monor violations.",
"User data is used to personalize ads.",
"Businesses can analyze performance via Facebook Insights.",
"Business pages differ from personal profiles.",
"Facebook Gaming allows streaming and watching video games.",
"Messenger emojis add quick reactions.",
"AR filters and effects are popular in stories.",
"Facebook Dating is an online dating feature.",
"Users can archive or delete old pots.",
"Hashtags help categorize content.",
"Memes and viral content are often shared on Facebook.",
"Private groups require an invitation to join.",
"Polls help engage with audiences."
"Facebook partners with charities for fundraisers.",
"Notifications alert users to new activity.",
"Users can customize their profile with a cover photo.",
"Auto-translation helps understand posts in different languages.",
"Videos can be saved into playlists.",
"Facebook offers accessibility features for the visually impaired.",
"Users can block other accounts if needed."
"Trending topics show popular discussions.",
"Pinned posts stay at the top of a profile.",
"Facebook owns Instagram and WhatsApp.",
"Users can create photo albums.",
"Mini-games are available via Facebook Instant Games.",
"Status updates let users share moods or thoughts.",
"Sharing expands content reach.",
"Facebook regularly evolves with new features.",
"New advancements in AI are reshaping industries, sparking discussions about ethics and innovation.",
"Cybersecurity remains a top priority as digital threats grow more sophisticated. #OnlineSafety",
"Climate change discussions gain momentum as extreme weather events increase globally.",
"Small daily actions—like reducing waste—can collectively make a big environmental impact.",
"Public art installations spark conversations and community engagement.",
"Cultural heritage preservation reminds us of our shared history. ",
"Travel trends show a rise in sustainable tourism options.",
"Exploring new cultures fosters understanding and connection. ",
"Global markets react to shifting trade policies and economic indicators. ",
"Small businesses adapt to challenges, proving resilience in uncertain times. #SupportLocal",
"A tight match this weekend between [Team A] and [Team B]! The score reflects the intensity on the field.",
"Athletes continue to push boundaries at [Event Name], showcasing dedication and perseverance. #Sportsmanship",
"Geopolitical tensions continue to evolve, underscoring the need for international dialogue to maintain stability."
"Educational groups on Facebook offer resources for learners.",
"Facebook’s ‘Learn with Facebook’ program provides free courses.",
"Teachers sometimes use Facebook Pages to share class updates.",
"Academic discussions thrive in niche Facebook communities. ",
"Facebook remains one of the most widely used platforms for global communication. #DigitalConnections",
"Many small businesses rely on Facebook to reach their customers.",
"Facebook Groups provide spaces for people to discuss shared interests.",
"Privacy settings on Facebook help users control their online presence.",
"Facebook Marketplace has become a popular tool for local buying and selling. #Ecommerce",
"Facebook continues to evolve with new features like Stories and Reels.",
"The Facebook algorithm determines what content appears in your feed. #SocialMedia",
"Facebook’s fact-checking program aims to reduce misinformation.",
"Many users access Facebook through its mobile app for convenience.",
"Facebook’s integration with Instagram and WhatsApp expands its ecosystem.",
"Facebook Ads allow businesses to target specific demographics.",
"Facebook Insights provides analytics for page performance.",
"Brands use Facebook Live to engage with audiences in real-time.",
"Facebook’s ad policies are regularly updated to ensure compliance.",
"Customer service via Facebook Messenger is now a common practice. ",
"Climate change is no longer a distant threat—it's happening now, and we must act urgently,",
"Rising global temperatures are disrupting ecosystems and weather patterns worldwide,",
"The last decade was the hottest on record, proving climate change is accelerating,",
"Melting glaciers and rising sea levels threaten coastal communities everywhere,",
"Extreme weather events like hurricanes and wildfires are becoming more intense due t,o climate change,",
"Deforestation continues to worsen climate change by reducing Earth's carbon absorptio,n capacity,",
"The ocean absorbs most of the planet's excess heat, causing devastating marine ecosystem changes,",
"Climate change disproportionately affects vulnerable communities with the fewest resources,",
"Air pollution from fossil fuels contributes to both climate change and public health crises,",
"Renewable energy adoption is growing, but we need faster implementation to meet climate goals,",
"Solar power has become one of the cheapest energy sources, yet fossil fuels still dominate,",
"Wind energy could power much of the world if we invest in the necessary infrastructure,",
"Electric vehicles are improving, but we need better charging networks to make them truly accessible,",
"Sustainable agriculture practices can help reduce the food industry's massive carbon footprint,",
"Plant-based diets require far fewer resources and generate less emissions than meat production,",
"Fast fashion is a major polluter—buying less and recycling clothes helps fight climate change,",
"Single-use plastics not only pollute our oceans but also contribute to greenhouse gas emissions,",
"Carbon capture technology shows promise but shouldn't replace emission reduction efforts,",
"Urban green spaces help combat climate change while improving city residents' quality of life,",
"Climate refugees are becoming more common as some areas become uninhabitable,",
"Permafrost thawing in the Arctic releases dangerous methane, accelerating global warming,",
"Coral reefs are dying at alarming rates due to ocean acidification and warming waters,",
"The economic costs of climate change already exceed hundreds of billions annually,",
"Investing in climate resilience now will save countless lives and resources in the future,",
"Youth climate activists are reminding world leaders that our future is at stake,",
"Climate change is causing some animal species to migrate or face possible extinction,",
"Winter seasons are becoming shorter in many regions, disrupting natural cycles,",
"Droughts are lasting longer and becoming more severe due to shifting climate patterns,",
"Climate misinformation spreads easily online, making public education more important than ever,",
"Every fraction of a degree in global temperature rise makes a significant difference,",
"Climate justice means ensuring solutions don't burden already disadvantaged communities,",
"Remote work and reduced business travel could significantly lower corporate carbon footprints,",
"Simple actions like proper home insulation can dramatically reduce energy consumption,",
"Food waste contributes significantly to methane emissions when it decomposes in landfills,",
"Climate anxiety is a growing mental health concern, especially among younger generations,",
"Traditional ecological knowledge from indigenous communities offers valuable climate solutions,",
"The insurance industry is increasingly factoring climate risks into coverage and pricing,",
"Climate change is altering growing seasons, creating challenges for farmers worldwide,",
"Microplastics are now found everywhere—from mountaintops to deep ocean trenches,",
"Green roofs in cities help reduce the urban heat island effect while absorbing rainwater,",
"Climate models show we still have time to avoid the worst scenarios if we act now,",
"Overfishing combined with climate change is pushing many marine species toward collapse,",
"Battery storage technology is improving but remains a bottleneck for renewable energy,"
"Climate change is increasing the range of disease-carrying insects like mosquitoes,",
"Thawing glaciers are revealing ancient viruses and bacteria frozen for millennia,",
"The shipping industry accounts for significant emissions but receives little regulation,",
"Climate education should be mandatory in schools to prepare future generations,",
"Personal carbon footprints vary dramatically between wealthy and developing nations,",
"Climate change mitigation creates millions of new jobs in green energy sectors,",
"While the challenges seem overwhelming, every positive action contributes to the solution,"
]


# Créer un DataFrame pour les tweets neutres
df_neutral = pd.DataFrame({
    "sentiment": [2] * len(neutral_examples),  # Sentiment neutre (label = 2)
    "text": neutral_examples,
    "processed_text": neutral_examples  # Valeur par défaut pour processed_text
})

# Convertir les textes neutres en minuscules
df_neutral["processed_text"] = df_neutral["processed_text"].str.lower()

# Sauvegarder les tweets neutres dans un fichier séparé
df_neutral.to_csv("data/tweets_neutral1.csv", index=False, encoding='utf-8', mode='a', header=False)


print("Fichier contenant les tweets neutres sauvegardé sous 'data/tweets_neutral1.csv'. append")