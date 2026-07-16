"use client";

import { ReactNode } from "react";
import { Provider } from "react-redux";
import { PersistGate } from "redux-persist/integration/react";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";

import { store, persistor } from "@/app/_lib/store";

// import { persistQueryClient } from "@tanstack/react-query-persist-client";
// import { createSyncStoragePersister } from "@tanstack/query-sync-storage-persister";
// import milisec from "@/app/_lib/milisec";

const queryClient = new QueryClient({
	// defaultOptions: {
	// 	queries: {
	// 		gcTime: milisec({ day: 7 }),
	// 	},
	// },
});

// const persister = createSyncStoragePersister({
// 	storage: window.indexedDB,
// });

// persistQueryClient({
// 	queryClient,
// 	persister,
// 	maxAge: milisec({ day: 7 }),
// });

type ProvidersProps = {
	children: ReactNode;
};

export default function Providers({ children }: ProvidersProps) {
	return (
		<Provider store={store}>
			<PersistGate loading={null} persistor={persistor}>
				<QueryClientProvider client={queryClient}>
					{children}
					<ReactQueryDevtools initialIsOpen={false} />
				</QueryClientProvider>
			</PersistGate>
		</Provider>
	);
}