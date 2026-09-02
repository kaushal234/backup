import { client } from "../store";

interface IDeleteSupplierCorrectiveActionRequestMainFileApiParams {
  id: string;
  mainFileId: string;
}

export const deleteSupplierCorrectiveActionRequestMainFile = async ({
  id,
  mainFileId,
}: IDeleteSupplierCorrectiveActionRequestMainFileApiParams) => {
  try {
    await client.delete(
      `/quality/supplier_corrective_action_requests/${id}/main_file/${mainFileId}`
    );
    return {
      status: 200,
      data: null,
    };
  } catch (error) {
    console.error(error);
    return { status: 500 };
  }
};
